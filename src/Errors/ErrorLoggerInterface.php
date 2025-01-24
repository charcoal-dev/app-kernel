<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

/**
 * Interface ErrorLoggerInterface
 * @package Charcoal\App\Kernel\Errors
 */
interface ErrorLoggerInterface
{
    /**
     * Log given ErrorEntry or \Throwable object
     */
    public function write(\Throwable|ErrorEntry $error): void;
}