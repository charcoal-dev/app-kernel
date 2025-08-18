<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Class LockedEntity
 * @package Charcoal\App\Kernel\Orm\Entity
 */
readonly class LockedEntity
{
    public function __construct(
        public OrmEntityBase          $entity,
        public SemaphoreLockInterface $lock,
    )
    {

    }
}