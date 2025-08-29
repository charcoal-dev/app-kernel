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
 * ErrorBoundary is a final class designed to serve as a mechanism for capturing and handling errors
 * within specific areas of the application. It helps isolate error-prone operations and ensures
 * that any unforeseen issues do not propagate beyond the defined boundary.
 * @api
 */
final class ErrorBoundary
{
    /**
     * Terminates the application by outputting error details based on the SAPIs type.
     * @api
     */
    public static function terminate(SapiType $sapi, AppCrashException|\Throwable $exception): never
    {
        if ($exception instanceof AppCrashException) {
            $exception = $exception->getPrevious();
        }

        if ($sapi === SapiType::Cli) {
            $exceptionDto = json_encode(ErrorHelper::getExceptionDto($exception));
            error_log($exceptionDto);
            fwrite(STDERR, $exceptionDto);
            exit(1);
        }

        header("Content-Type: application/json");
        print(json_encode(ErrorHelper::getExceptionDto($exception)));
        exit(1);
    }
}