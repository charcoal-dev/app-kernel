<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Errors;

use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * Interface ErrorLoggerInterface
 * @package Charcoal\App\Kernel\Contracts\Errors
 */
interface ErrorLoggerInterface
{
    public function handleError(ErrorEntry $error): void;

    public function handleException(\Throwable $exception): void;
}