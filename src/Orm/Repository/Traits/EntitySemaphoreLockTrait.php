<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Entity\SemaphoreLockHooksInterface;
use Charcoal\App\Kernel\Orm\Entity\LockedEntity;
use Charcoal\App\Kernel\Orm\Exception\EntityLockedException;
use Charcoal\Database\Enums\LockFlag;
use Charcoal\Semaphore\Exceptions\SemaphoreUnlockException;

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
     * @throws \Charcoal\Semaphore\Exceptions\SemaphoreLockException
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
            throw new EntityLockedException($this->table->entityClass, previous: $e);
        }

        if ($autoReleaseLock) {
            try {
                $lock->setAutoRelease();
            } catch (SemaphoreUnlockException) {
            }
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
        } catch (\Exception $e) {
            try {
                $lock->releaseLock();
            } catch (\Exception) {
            }

            throw $e;
        }
    }
}