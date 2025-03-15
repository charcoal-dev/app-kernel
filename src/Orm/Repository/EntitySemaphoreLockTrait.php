<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Orm\Entity\LockedEntity;
use Charcoal\App\Kernel\Orm\Entity\SemaphoreLockHooksInterface;
use Charcoal\App\Kernel\Orm\Exception\EntityLockedException;
use Charcoal\Database\Queries\LockFlag;

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
     * @throws \Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException
     * @throws \Charcoal\App\Kernel\Orm\Exception\EntityOrmException
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
            $lock = $this->module->getSemaphore()->obtainLock(
                $entityLockId,
                $lockTimeout > 0 ? $lockCheckEvery : null,
                $lockTimeout
            );
        } catch (\Exception $e) {
            throw new EntityLockedException(previous: $e);
        }

        /** @noinspection PhpUnreachableStatementInspection */
        if ($autoReleaseLock) {
            $lock->setAutoRelease();
        }

        try {
            $entity = $this->getFromDb($whereStmt, $queryData, $dbLockFlag);
            if ($entity instanceof SemaphoreLockHooksInterface) {
                $lifecycleLog = $entity->onLockObtained();
                if ($lifecycleLog) {
                    $this->module->app->lifecycle->log($lifecycleLog, null, true);
                }
            }

            return new LockedEntity($entity, $lock);
        } catch (\Exception $t) {
            try {
                $lock->releaseLock();
            } catch (\Exception) {
            }

            throw $t;
        }
    }
}