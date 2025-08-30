<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Internal\Exceptions\AppCrashException;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\App\Kernel\Support\Errors\ErrorBoundary;
use Charcoal\Base\Traits\ControlledSerializableTrait;
use Charcoal\Base\Traits\InstanceOnStaticScopeTrait;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Manages application error handling, logging, and exception processing.
 * Implements configuration for error policies, log levels, and error propagation.
 */
final class ErrorManager implements AppServiceInterface
{
    use InstanceOnStaticScopeTrait;
    use ControlledSerializableTrait;
    use NoDumpTrait;
    use NotCloneableTrait;

    public const array FATAL_ERRORS = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR,
        E_PARSE, E_USER_ERROR, E_RECOVERABLE_ERROR];

    public const array LOGGABLE_LEVELS = [E_NOTICE, E_USER_NOTICE,
        E_DEPRECATED, E_USER_DEPRECATED, E_WARNING, E_USER_WARNING];

    private static int $debugBacktraceOffset = 0;
    private static bool $handlingThrowable = false;
    private static bool $handlersSet = false;

    private ErrorLoggers $loggers;
    private array $loggable;
    public readonly bool $policy;
    public readonly int $pathOffset;
    public bool $debugging;

    /**
     * @param AppEnv $env
     * @param PathRegistry $paths
     * @param ErrorLoggers|null $loggers
     */
    public function __construct(AppEnv $env, PathRegistry $paths, ?ErrorLoggers $loggers = null)
    {
        $this->policy = $env->deployErrorHandlers();
        $this->pathOffset = strlen($paths->root->absolute);
        $this->debugging = $env->isDebug();
        $this->loggable = [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED];
        $this->loggers = $loggers ?? new ErrorLoggers();
        register_shutdown_function([$this, "handleShutdown"]);
    }

    /**
     * @param ErrorLoggerInterface $logger
     * @return void
     */
    final public function subscribe(ErrorLoggerInterface $logger): void
    {
        $this->loggers->register($logger);
    }

    /**
     * @param ErrorLoggerInterface $logger
     * @return void
     * @api
     */
    final public function unsubscribe(ErrorLoggerInterface $logger): void
    {
        $this->loggers->unregister($logger);
    }

    /**
     * @param int ...$levels
     * @return void
     * @api
     */
    final public function setLoggableErrors(int ...$levels): void
    {
        $this->loggable = [];
        $levels = array_unique($levels);
        foreach ($levels as $level) {
            if (!in_array($level, self::LOGGABLE_LEVELS)) {
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
        return is_int($trims) && $trims >= 0 ? self::$debugBacktraceOffset = $trims :
            self::$debugBacktraceOffset;
    }

    /**
     * @return bool
     * @api
     */
    final public function hasHandlersSet(): bool
    {
        return self::$handlersSet;
    }

    /**
     * Initializes Error & Exception handlers. Called on construct and unserialize
     * @return void
     * @internal
     */
    final public function setHandlers(): void
    {
        if (self::$handlersSet) {
            return;
        }

        self::$handlersSet = true;
        set_error_handler([$this, "handleError"]);
        set_exception_handler([$this, "handleThrowable"]);
    }

    /**
     * @return array
     */
    protected function collectSerializableData(): array
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
     * @param array $data
     * @return void
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
     * @return array
     */
    public static function unserializeDependencies(): array
    {
        return [self::class, PathInfo::class, ErrorLoggers::class];
    }

    /**
     * @api
     * @noinspection PhpUnhandledExceptionInspection
     * @deprecated
     */
    final public function trigger(
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
            $trace[$fileLineBacktraceIndex]["file"] ?? "~",
            intval($trace[$fileLineBacktraceIndex]["line"] ?? -1));
    }

    /**
     * @return void
     * @throws AppCrashException
     * @internal
     */
    final public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && $this->isFatalError($error["type"])) {
            $this->handleThrowable(
                new \ErrorException($error["message"], 0, $error["type"], $error["file"], $error["line"])
            );
        }
    }

    /**
     * Default exception handler function
     * @param \Throwable $t
     * @return void
     * @throws AppCrashException
     * @internal
     */
    final public function handleThrowable(\Throwable $t): void
    {
        if (self::$handlingThrowable) {
            return;
        }

        self::$handlingThrowable = true;
        $this->loggers->handleException($t);
        set_exception_handler(fn(AppCrashException|\Throwable $e) => ErrorBoundary::terminate($e));
        throw new AppCrashException($t);
    }

    /**
     * @throws \Exception
     */
    final public function handleError(int $level, string $message, string $file, int $line): bool
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
     */
    public static function isFatalError(int $level): bool
    {
        return in_array($level, self::FATAL_ERRORS, true);
    }
}