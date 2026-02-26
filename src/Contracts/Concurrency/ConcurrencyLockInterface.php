<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Concurrency;

use Charcoal\App\Kernel\Concurrency\Locks\LockAcquireOptions;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Represents an interface for handling concurrency locks.
 * This interface provides a contract for managing locks on shared resources,
 * ensuring proper acquisition and release of locks in concurrent environments.
 */
interface ConcurrencyLockInterface
{
    public LockAcquireOptions $resource {
        get;
    }

    public SemaphoreLockInterface $lock {
        get;
    }
}