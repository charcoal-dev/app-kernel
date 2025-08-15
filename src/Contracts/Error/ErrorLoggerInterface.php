<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Error;

use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * Interface ErrorLoggerInterface
 * @package Charcoal\App\Kernel\Contracts\Error
 */
interface ErrorLoggerInterface
{
    /**
     * Log given ErrorEntry or \Throwable object
     */
    public function write(\Throwable|ErrorEntry $error): void;
}