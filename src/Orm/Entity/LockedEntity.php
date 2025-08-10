<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

use Charcoal\Semaphore\AbstractLock;

/**
 * Class LockedEntity
 * @package Charcoal\App\Kernel\Orm\Entity
 */
readonly class LockedEntity
{
    public function __construct(
        public AbstractOrmEntity $entity,
        public AbstractLock      $lock,
    )
    {

    }
}