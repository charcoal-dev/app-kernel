<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Internal\Exceptions\AppCrashException;
use Charcoal\App\Kernel\Support\ErrorHelper;

/**
 * ErrorBoundary is an abstract class designed to serve as a mechanism for capturing and handling errors
 * within specific areas of the application. It helps isolate error-prone operations and ensures
 * that any unforeseen issues do not propagate beyond the defined boundary.
 * @api
 */
abstract class ErrorBoundary
{
    public static bool $writeErrorLog = true;
    public static bool $writeStdError = false;
    public static int $pathOffset = 0;

    private static ?\Closure $exceptionHandler = null;

    /**
     * @return class-string<self>
     * @api
     */
    public static function configStreams(bool $errorLog, bool $stdError, int $pathOffset): string
    {
        static::$writeErrorLog = $errorLog;
        static::$writeStdError = $stdError;
        static::$pathOffset = $pathOffset;
        return static::class;
    }

    /**
     * Configure PHP to log errors to the Docker standard error stream.
     * @return class-string<self>
     * @api
     */
    public static function alignDockerStdError(): string
    {
        ini_set("log_errors", "On");
        ini_set("error_log", "/proc/self/fd/2");
        return static::class;
    }

    /**
     * @param \Closure $callback
     * @return class-string<self>
     */
    final public static function handle(\Closure $callback): string
    {
        static::$exceptionHandler = $callback;
        return static::class;
    }

    /**
     * Terminates the application by outputting error details based on the SAPIs type.
     * @noinspection PhpUnhandledExceptionInspection
     * @api
     */
    public static function terminate(AppCrashException|\Throwable $exception): never
    {
        static::toErrorStream($exception, self::$writeErrorLog, self::$writeStdError, self::$pathOffset);
        if (static::$exceptionHandler) {
            try {
                call_user_func(static::$exceptionHandler, $exception);
            } catch (\Throwable $e) {
                static::toErrorStream($e, $errorLog, $stdError, $pathOffset);
                exit(1);
            }
        }

        exit(1);
    }

    /**
     * Converts an exception into an error stream output, optionally logging or writing the output to STDERR.
     * @throws \JsonException
     */
    public static function toErrorStream(
        AppCrashException|\Throwable $exception,
        bool                         $errorLog = true,
        bool                         $stdError = false,
        int                          $pathOffset = 0,
    ): array|string
    {
        if ($exception instanceof AppCrashException) {
            $exception = $exception->getPrevious();
        }

        $exceptionDto = json_encode(
            ErrorHelper::getExceptionDto($exception, pathOffset: $pathOffset),
            flags: JSON_THROW_ON_ERROR
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_INVALID_UTF8_SUBSTITUTE
            | JSON_PRESERVE_ZERO_FRACTION
        );

        if (!$exceptionDto) {
            $exceptionStr = ErrorHelper::exception2String($exception);
        }

        if ($stdError) {
            error_log($exceptionDto ?: $exceptionStr);
        }

        if ($errorLog) {
            fwrite(STDERR, $exceptionDto ?: $exceptionStr);
        }

        return $exceptionDto;
    }
}