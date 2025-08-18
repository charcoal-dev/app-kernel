<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Errors;

use Charcoal\App\Kernel\Errors\ErrorEntry;

/**

 *  Since this implements ErrorLoggerInterface contract, therefore it can be expected
 *  to be used with ErrorManager service, therefore, always extend this class so that
 *  ErrorManager subscription doesn't override.
 */
interface ErrorLoggerInterface
{
    public function handleError(ErrorEntry $error): void;

    public function handleException(\Throwable $exception): void;
}