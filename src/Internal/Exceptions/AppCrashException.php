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
 */
class AppCrashException extends \RuntimeException
{
}