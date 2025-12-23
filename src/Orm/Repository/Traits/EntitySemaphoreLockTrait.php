<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Entity\SemaphoreLockHooksInterface;
use Charcoal\App\Kernel\Diagnostics\Diagnostics;
use Charcoal\App\Kernel\Orm\Entity\LockedEntity;
use Charcoal\App\Kernel\Orm\Exceptions\EntityLockedException;
use Charcoal\Database\Enums\LockFlag;

/**
 * Trait EntitySemaphoreLockTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntitySemaphoreLockTrait
{
    /**
     * @param string $entityLockId
     * @param string $whereStmt
     * @param array $queryData
     * @param int $lockTimeout
     * @param float $lockCheckEvery
     * @param bool $autoReleaseLock
     * @param LockFlag|null $dbLockFlag
     * @return LockedEntity
     * @throws EntityLockedException
     */
    protected function getLockedEntity(
        string    $entityLockId,
        string    $whereStmt,
        array     $queryData = [],
        int       $lockTimeout = 0,
        float     $lockCheckEvery = 0.25,
        bool      $autoReleaseLock = true,
        ?LockFlag $dbLockFlag = null,
    ): LockedEntity
    {
        try {
            $lock = $this->module->getSemaphoreLock(
                $entityLockId,
                $lockTimeout > 0 ? $lockCheckEvery : null,
                $lockTimeout
            );
        } catch (\Exception $e) {
            throw new EntityLockedException($this->table->entityClass, previous: $e);
        }

        if ($autoReleaseLock) {
            $lock->setAutoRelease();
        }

        try {
            $entity = $this->getFromDb($whereStmt, $queryData, $dbLockFlag);
            if ($entity instanceof SemaphoreLockHooksInterface) {
                $logEntry = $entity->onLockObtained();
                if ($logEntry) {
                    Diagnostics::app()->verbose($logEntry);
                }
            }

            return new LockedEntity($entity, $lock);
        } catch (\Exception $e) {
            try {
                $lock->releaseLock();
            } catch (\Exception) {
            }

            throw new EntityLockedException($this->table->entityClass, previous: $e);
        }
    }
}