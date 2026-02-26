<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency\Locks;

use Charcoal\App\Kernel\Concurrency\ConcurrencyService;
use Charcoal\App\Kernel\Contracts\Concurrency\ConcurrencyLockInterface;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Represents an abstract, immutable action lock mechanism that uses semaphore for synchronization.
 * @api
 */
abstract readonly class AbstractLockHeld implements ConcurrencyLockInterface
{
    public function __construct(
        public LockAcquireOptions     $resource,
        public SemaphoreLockInterface $lock,
        array                         $args = [],
    )
    {
    }

    /**
     * @throws \Charcoal\App\Kernel\Concurrency\ConcurrencyLockException
     * @api
     */
    protected static function acquire(
        ConcurrencyService $concurrency,
        LockAcquireOptions $resource,
        array              $args = [],
    ): static
    {
        $lock = $concurrency->acquireLock($resource);
        return new static($resource, $lock, $args);
    }
}