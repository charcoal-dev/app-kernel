<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency\Locks;

use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Represents an abstract, immutable action lock mechanism that uses semaphore for synchronization.
 */
abstract readonly class AbstractLockHeld
{
    public function __construct(
        public string                 $lockId,
        public SemaphoreLockInterface $lock,
    )
    {
    }
}
