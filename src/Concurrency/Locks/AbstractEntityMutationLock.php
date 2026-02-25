<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency\Locks;

use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Represents an abstract lock mechanism designed to guard mutation processes
 * associated with a specific ORM entity. This class extends from the general
 * action lock to provide entity-specific locking behavior.
 */
abstract readonly class AbstractEntityMutationLock extends AbstractLockHeld
{
    public function __construct(
        string                 $lockId,
        SemaphoreLockInterface $lock,
        public OrmEntityBase   $entity,
    )
    {
        parent::__construct($lockId, $lock);
    }
}