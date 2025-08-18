<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Class ErrorManager
 * @package Charcoal\App\Kernel\Errors
 */
class ErrorManager implements AppServiceInterface
{
    public const array FATAL_ERRORS = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR,
        E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR];

    public const array LOGGABLE_LEVELS = [E_NOTICE, E_USER_NOTICE,
        E_DEPRECATED, E_USER_DEPRECATED];

    private static ?self $instance = null;
    private static bool $handlingThrowable = false;
    private static bool $handlersSet = false;

    private static int $debugBacktraceOffset = 3;

    private ErrorLoggers $loggers;
    private array $loggable;
    public readonly bool $policy;
    public readonly int $pathOffset;
    public bool $debugging;

    use NoDumpTrait;
    use NotCloneableTrait;

    /**
     * @param AppEnv $env
     * @param PathInfo $root
     * @param ErrorLoggers|null $loggers
     */
    protected function __construct(AppEnv $env, PathInfo $root, ErrorLoggers $loggers = null)
    {
        $this->policy = $env->deployErrorHandlers();
        $this->pathOffset = strlen($root->absolute);
        $this->debugging = $env->isDebug();
        $this->loggable = static::LOGGABLE_LEVELS;
        $this->loggers = $loggers ?? new ErrorLoggers();
    }

    /**
     * @param AppEnv $env
     * @param PathInfo $root
     * @param ErrorLoggers|null $loggers
     * @return static
     */
    public static function initialize(AppEnv $env, PathInfo $root, ErrorLoggers $loggers = null): static
    {
        if (isset(static::$instance)) {
            throw new \LogicException("Errors service instance already exists");
        }

        return static::$instance = new self($env, $root, $loggers);
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (!isset(static::$instance)) {
            throw new \LogicException("Errors service instance not initialized");
        }

        return static::$instance;
    }

    /**
     * @param int ...$levels
     * @return void
     * @api
     */
    public function setLoggableErrors(int ...$levels): void
    {
        $this->loggable = [];
        $levels = array_unique($levels);
        foreach ($levels as $level) {
            if (!in_array($level, static::LOGGABLE_LEVELS)) {
                throw new \LogicException('Invalid error level to log: ' . $level);
            }

            $this->loggable[] = $level;
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
     * @internal
     */
    public function __serialize(): array
    {
        return [
            "policy" => $this->policy,
            "pathOffset" => $this->pathOffset,
            "debugging" => $this->debugging,
            "loggable" => $this->loggable,
            "loggers" => null,
        ];
    }

    /**
     * @internal
     */
    public function __unserialize(array $data): void
    {
        $this->policy = $data["policy"];
        $this->pathOffset = $data["pathOffset"];
        $this->debugging = $data["debugging"];
        $this->loggable = $data["loggable"];
        $this->loggers = new ErrorLoggers();
    }

    /**
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

        if (in_array($level, $this->loggable)) {
            $err = new ErrorEntry($this, $level, $message, $file, $line);
            $this->loggers->handleError($err);
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
            "file" => static::getRelativeFilepath($t->getFile()),
            "line" => $t->getLine(),
        ];

        if ($this->debugging) {
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
    public static function isFatalError(int $level): bool
    {
        return in_array($level, static::FATAL_ERRORS, true);
    }
}