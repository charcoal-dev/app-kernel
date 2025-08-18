<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Stubs;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * Class NullErrorLog
 * @package Charcoal\App\Kernel\Stubs
 */
final class NullErrorLog implements ErrorLoggerInterface
{
    public function handleError(ErrorEntry $error): void
    {
    }

    public function handleException(\Throwable $exception): void
    {
    }
}