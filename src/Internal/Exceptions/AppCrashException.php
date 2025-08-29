<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Exceptions;

/**
 * Represents an exception thrown when an application crashes or encounters a critical failure.
 * Extends the base Exception class to provide specific handling for application-level crashes.
 * @internal Do NOT catch except on final error boundary!
 */
final class AppCrashException extends \RuntimeException
{
    public function __construct(\Throwable $previous)
    {
        parent::__construct("Application crashed with " . $previous::class .
            "; Execution terminated", 0, $previous);
    }
}