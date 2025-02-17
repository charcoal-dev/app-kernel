<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Build\AppBuildEnum;
use Charcoal\App\Kernel\Errors;
use Charcoal\OOP\Traits\NoDumpTrait;
use Charcoal\OOP\Traits\NotCloneableTrait;
use Charcoal\OOP\Traits\NotSerializableTrait;

/**
 * Class ErrorHandler
 * @package Charcoal\App\Kernel\Errors
 */
class ErrorHandler implements \IteratorAggregate
{
    private static bool $handlingThrowable = false;
    private static bool $handlersSet = false;

    public readonly int $pathOffset;
    public int $debugBacktraceLevel = E_WARNING;
    public int $backtraceOffset = 3;
    public bool $exceptionHandlerShowTrace = true;

    private array $errorLoggable;
    private array $errorLog;
    private int $errorLogCount;

    use NoDumpTrait;
    use NotSerializableTrait;
    use NotCloneableTrait;

    /**
     * @param AppBuild $app
     * @param AppBuildEnum $build
     * @param ErrorLoggerInterface $logger
     */
    public function __construct(AppBuild $app, AppBuildEnum $build, public ErrorLoggerInterface $logger)
    {
        $this->pathOffset = strlen($app->directories->root->path);
        $this->errorLoggable = [];
        $this->errorLog = [];
        $this->errorLogCount = 0;
        if ($build->setErrorHandlers()) {
            $this->setHandlers();
        }
    }

    /**
     * @param int ...$levels
     * @return void
     */
    public function setLoggableErrors(int ...$levels): void
    {
        $this->errorLoggable = [];
        $levels = array_unique($levels);
        foreach ($levels as $level) {
            if (!in_array($level, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])) {
                throw new \LogicException('Invalid error level to log: ' . $level);
            }

            $this->errorLoggable[] = $level;
        }
    }

    /**
     * @return bool
     */
    public function hasHandlersSet(): bool
    {
        return static::$handlersSet;
    }

    /**
     * Initializes Error & Exception handlers. Called on construct and unserialize
     * @return void
     */
    public function setHandlers(): void
    {
        if (static::$handlersSet) {
            return;
        }

        static::$handlersSet = true;
        set_error_handler([$this, "handleError"]);
        set_exception_handler([$this, "handleThrowable"]);
        register_shutdown_function([$this, "handleShutdown"]);
    }

    /**
     * Erases entire error log, returning all existing values in an Array
     * @return array
     */
    public function flushClean(): array
    {
        $errorLog = $this->errorLog;
        $this->flush();
        return $errorLog;
    }

    /**
     * Erases entire error log
     * @return void
     */
    public function flush(): void
    {
        $this->errorLog = [];
        $this->errorLogCount = 0;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        if ($this->errorLogCount > 0) {
            throw new \LogicException('App instance cannot be serialized with errors logged');
        }

        return [
            "errorLoggable" => $this->errorLoggable,
            "pathOffset" => $this->pathOffset,
            "debugBacktraceLevel" => $this->debugBacktraceLevel,
            "backtraceOffset" => $this->backtraceOffset,
            "exceptionHandlerShowTrace" => $this->exceptionHandlerShowTrace
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->pathOffset = $data["pathOffset"];
        $this->debugBacktraceLevel = $data["debugBacktraceLevel"];
        $this->backtraceOffset = $data["backtraceOffset"];
        $this->exceptionHandlerShowTrace = $data["exceptionHandlerShowTrace"];
        $this->errorLoggable = $data["errorLoggable"];
        $this->errorLog = [];
        $this->errorLogCount = 0;
    }

    /**
     * @param string|\Throwable $error
     * @param int $level
     * @param int $fileLineBacktraceIndex
     * @return void
     * @throws \ErrorException
     */
    public function trigger(string|\Throwable $error, int $level = E_USER_NOTICE, int $fileLineBacktraceIndex = 1): void
    {
        if ($error instanceof \Throwable) {
            $error = \Charcoal\App\Kernel\Errors::Exception2String($error);
        }

        if (!in_array($level, [E_USER_NOTICE, E_USER_WARNING])) {
            throw new \InvalidArgumentException('Invalid error level to trigger');
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->handleError($level, $error,
            $trace[$fileLineBacktraceIndex]["file"] ?? "",
            intval($trace[$fileLineBacktraceIndex]["line"] ?? -1));
    }

    /**
     * Appends error message in runtime memory
     * @param ErrorEntry $errorMsg
     * @return void
     */
    public function append(ErrorEntry $errorMsg): void
    {
        $this->errorLog[] = $errorMsg;
        $this->errorLogCount++;
    }

    /**
     * Returns number of errors currently in runtime memory
     * @return int
     */
    public function count(): int
    {
        return $this->errorLogCount;
    }

    /**
     * Method required by \IteratorAggregate interface, returns \ArrayIterator
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->errorLog);
    }

    /**
     * Returns all logged errors
     * @return array
     */
    public function getAll(): array
    {
        return $this->errorLog;
    }

    /**
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && $this->isFatalError($error["type"])) {
            $this->handleThrowable(new \ErrorException(
                    $error["message"],
                    0,
                    $error["type"],
                    $error["file"],
                    $error["line"]
                )
            );
        }
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws \ErrorException
     */
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (error_reporting() === 0) return false;

        if ($this->isFatalError($level)) {
            $this->handleThrowable(new \ErrorException($message, 0, $level, $file, $line));
        }

        if (in_array($level, $this->errorLoggable)) {
            $err = new ErrorEntry($this, $level, $message, $file, $line);
            $this->append($err);

            try {
                $this->logger->write($err);
            } catch (\Exception $e) {
                throw new \ErrorException(Errors::Exception2String($e), 0);
            }

            return true;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Default exception handler function
     * @param \Throwable $t
     * @return never
     */
    public function handleThrowable(\Throwable $t): never
    {
        if (static::$handlingThrowable) {
            exit(1);
        }

        static::$handlingThrowable = true;
        $exception = [
            "class" => get_class($t),
            "message" => $t->getMessage(),
            "code" => $t->getCode(),
            "file" => $this->getOffsetFilepath($t->getFile()),
            "line" => $t->getLine(),
        ];

        if ($this->exceptionHandlerShowTrace) {
            $exception["trace"] = $t->getTrace();
        }

        if (php_sapi_name() !== "cli") {
            header("Content-Type: application/json", response_code: 500);
        }

        exit(json_encode(["FatalError" => $exception]));
    }

    /**
     * Returns neat filepath
     * @param string $path
     * @return string
     */
    final public function getOffsetFilepath(string $path): string
    {
        return trim(substr($path, $this->pathOffset), DIRECTORY_SEPARATOR);
    }

    /**
     * @param int $level
     * @return bool
     */
    public function isFatalError(int $level): bool
    {
        return in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR]);
    }

    /**
     * Converts error level integer to appropriate string
     * @param int $level
     * @return string
     */
    final public function getErrorLevelStr(int $level): string
    {
        return match ($level) {
            1 => "Fatal Error",
            2, 512 => "Warning",
            4 => "Parse Error",
            8, 1024 => "Notice",
            16 => "Core Error",
            32 => "Core Warning",
            64 => "Compile Error",
            128 => "Compile Warning",
            256 => "Error",
            2048 => "Strict",
            4096 => "Recoverable",
            8192, 16384 => "Deprecated",
            default => "Unknown",
        };
    }
}