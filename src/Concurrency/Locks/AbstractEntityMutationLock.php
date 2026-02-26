<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency\Locks;

use Charcoal\App\Kernel\Concurrency\ConcurrencyLockException;
use Charcoal\App\Kernel\Concurrency\ConcurrencyService;
use Charcoal\App\Kernel\Contracts\Concurrency\ConcurrencyLockInterface;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Represents an abstract mechanism for handling concurrency locks
 * related to the mutation of a specific entity or set of entities.
 * This class ensures safe access and modification of resources
 * in concurrent environments by acquiring and managing a lock in
 * conjunction with the provided entity or entities.
 */
abstract readonly class AbstractEntityMutationLock implements ConcurrencyLockInterface
{
    public function __construct(
        public LockAcquireOptions     $resource,
        public SemaphoreLockInterface $lock,
        public array|OrmEntityBase    $entity,
    )
    {
    }

    /**
     * @throws ConcurrencyLockException
     * @api
     */
    public static function acquire(
        ConcurrencyService $concurrency,
        LockAcquireOptions $resource,
        \Closure           $fetchEntity
    ): self
    {
        $lock = $concurrency->acquireLock($resource);

        try {
            $entity = $fetchEntity();
            if (!is_array($entity) && !($entity instanceof OrmEntityBase)) {
                throw new ConcurrencyLockException("Entity must be an array or an instance of OrmEntityBase");
            }
        } catch (\Throwable $t) {
            $lock->releaseLock();
            throw new ConcurrencyLockException("Failed to retrieve entity for mutation lock",
                previous: $t);
        }

        return new static($resource, $lock, $entity);
    }
}