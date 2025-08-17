<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Contracts\Error\ErrorLoggerInterface;
use Charcoal\App\Kernel\Internal\AppEnv;
use Charcoal\App\Kernel\Internal\Config\ErrorManagerConfig;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Filesystem\Exceptions\FilesystemException;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\FilePath;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Class ErrorManager
 * @package Charcoal\App\Kernel\Errors
 */
class ErrorManager implements AppServiceInterface, \IteratorAggregate
{
    private static bool $handlingThrowable = false;
    private static bool $handlersSet = false;
    private static int $debugBacktraceOffset = 3;

    public readonly ErrorManagerConfig $policy;
    public readonly ?ErrorLoggerInterface $logger;
    public readonly int $pathOffset;
    private array $errorLoggable;
    private array $errorLog;

    use NoDumpTrait;
    use NotCloneableTrait;

    /**
     * @param AppEnv $env
     * @param DirectoryPath $root
     */
    public function __construct(public readonly AppEnv $env, PathInfo $root)
    {
        $this->policy = $env->errorManagerPolicy();
        $this->pathOffset = strlen($root->absolute);
        $this->errorLoggable = [E_NOTICE, E_USER_NOTICE];
        $this->errorLog = [];
        if ($this->policy->enabled) {
            try {
                $this->logger = is_string($this->policy->errorLogFile) ?
                    new FileErrorLogger(new FilePath($root->absolute . DIRECTORY_SEPARATOR .
                        $this->policy->errorLogFile)) : null;
            } catch (FilesystemException $e) {
                throw new \RuntimeException(sprintf("Failed to resolve error log file: [%s]: %s (path: %s)",
                    ObjectHelper::baseClassName($e),
                    $e->getMessage(),
                    $this->policy->errorLogFile
                ), previous: $e);
            }

            $this->setHandlers();
        }
    }

    /**
     * @param int ...$levels
     * @return void
     * @api
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
     * @param int|null $trims
     * @return int
     */
    public function debugBacktraceOffset(?int $trims): int
    {
        return is_int($trims) && $trims >= 0 ? static::$debugBacktraceOffset = $trims :
            static::$debugBacktraceOffset;
    }

    /**
     * @return bool
     * @api
     */
    public function hasHandlersSet(): bool
    {
        return static::$handlersSet;
    }

    /**
     * Initializes Error & Exception handlers. Called on construct and unserialize
     * @return void
     * @internal
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
     * Erases the entire error log, returning all existing values in an Array
     * @return array
     * @api
     */
    public function drain(): array
    {
        $errorLog = $this->errorLog;
        $this->flush();
        return $errorLog;
    }

    /**
     * Erases entire error log
     * @return void
     * @api
     */
    public function flush(): void
    {
        $this->errorLog = [];
    }

    /**
     * @return array
     * @internal
     */
    public function __serialize(): array
    {
        if (count($this->errorLog) > 0) {
            throw new \LogicException('App instance cannot be serialized with errors logged');
        }

        return [
            "env" => $this->env,
            "policy" => $this->policy,
            "pathOffset" => $this->pathOffset,
            "logger" => $this->logger,
            "errorLoggable" => $this->errorLoggable,
            "errorLog" => [],
        ];
    }

    /**
     * @param array $data
     * @return void
     * @internal
     */
    public function __unserialize(array $data): void
    {
        $this->env = $data["env"];
        $this->policy = $data["policy"];
        $this->pathOffset = $data["pathOffset"];
        $this->logger = $data["logger"];
        $this->errorLoggable = $data["errorLoggable"];
        $this->errorLog = [];
    }

    /**
     * @param string|\Throwable $error
     * @param int $level
     * @param int $fileLineBacktraceIndex
     * @return void
     * @throws \ErrorException
     * @api
     */
    public function trigger(
        string|\Throwable $error,
        int               $level = E_USER_NOTICE,
        int               $fileLineBacktraceIndex = 1
    ): void
    {
        if ($error instanceof \Throwable) {
            $error = \Charcoal\App\Kernel\Support\ErrorHelper::exception2String($error);
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
     * Appends an error message in runtime memory
     * @param ErrorEntry $errorMsg
     * @return void
     * @api
     */
    public function append(ErrorEntry $errorMsg): void
    {
        $this->errorLog[] = $errorMsg;
    }

    /**
     * Returns the number of errors currently in runtime memory
     * @return int
     * @api
     */
    public function count(): int
    {
        return count($this->errorLog);
    }

    /**
     * Returns all logged errors
     * @return array
     * @api
     */
    public function getAll(): array
    {
        return $this->errorLog;
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->errorLog);
    }

    /**
     * @return void
     * @internal
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
     * @internal
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
                throw new \ErrorException(ErrorHelper::exception2String($e), 0);
            }

            return true;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Default exception handler function
     * @param \Throwable $t
     * @return never
     * @internal
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
            "file" => $this->getRelativeFilepath($t->getFile()),
            "line" => $t->getLine(),
        ];

        if ($this->env->isDebug()) {
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
     * @internal
     */
    final public function getRelativeFilepath(string $path): string
    {
        return trim(substr($path, $this->pathOffset), DIRECTORY_SEPARATOR);
    }

    /**
     * @param int $level
     * @return bool
     * @internal
     */
    public function isFatalError(int $level): bool
    {
        return in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR]);
    }

    /**
     * Converts error level integer to appropriate string
     * @param int $level
     * @return string
     * @api
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