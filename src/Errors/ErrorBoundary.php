<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Enums\SapiType;
use Charcoal\App\Kernel\Internal\Exceptions\AppCrashException;
use Charcoal\App\Kernel\Support\ErrorHelper;

/**
 * ErrorBoundary is an abstract class designed to serve as a mechanism for capturing and handling errors
 * within specific areas of the application. It helps isolate error-prone operations and ensures
 * that any unforeseen issues do not propagate beyond the defined boundary.
 * @api
 */
abstract readonly class ErrorBoundary
{
    /**
     * Configure PHP to log errors to the Docker standard error stream.
     * @api
     */
    public static function alignDockerStdError(): void
    {
        ini_set("log_errors", "On");
        ini_set("error_log", "/proc/self/fd/2");
    }

    /**
     * Terminates the application by outputting error details based on the SAPIs type.
     * @api
     */
    public static function terminate(
        SapiType                     $sapi,
        AppCrashException|\Throwable $exception,
        bool                         $errorLog = true,
        bool                         $stdError = false,
        int                          $pathOffset = 0
    ): never
    {
        if ($exception instanceof AppCrashException) {
            $exception = $exception->getPrevious();
        }

        $exceptionDto = json_encode(ErrorHelper::getExceptionDto($exception, pathOffset: $pathOffset));

        if ($sapi === SapiType::Cli) {
            if ($stdError) {
                error_log($exceptionDto);
            }

            if ($errorLog) {
                fwrite(STDERR, $exceptionDto);
            }

            exit(1);
        }

        header("Content-Type: application/json");
        print(json_encode($exceptionDto));
        exit(1);
    }
}