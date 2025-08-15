<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Stubs;

use Charcoal\App\Kernel\Contracts\Error\ErrorLoggerInterface;
use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * Class NullErrorLog
 * @package Charcoal\App\Kernel\Stubs
 */
class NullErrorLog implements ErrorLoggerInterface
{
    /**
     * Does nothing with given ErrorEntry or \Throwable object >> /dev/null
     */
    public function write(\Throwable|ErrorEntry $error): void
    {
    }
}