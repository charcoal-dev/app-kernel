<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Polyfill;

use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\App\Kernel\Errors\ErrorLoggerInterface;

/**
 * Class NullErrorLog
 * @package Charcoal\App\Kernel\Polyfill
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