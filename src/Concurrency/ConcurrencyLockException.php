<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency;

/**
 * Represents an exception thrown when a concurrency lock conflict occurs.
 */
final class ConcurrencyLockException extends \Exception
{
}