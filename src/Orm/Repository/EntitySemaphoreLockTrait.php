<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Orm\Entity\SemaphoreLockHooksInterface;
use Charcoal\Database\Queries\LockFlag;
use Charcoal\Semaphore\AbstractLock;

/**
 * Trait EntitySemaphoreLockTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntitySemaphoreLockTrait
{
    abstract protected function getEntityLockId(AbstractOrmEntity $entity): string;

    /**
     * @param AbstractLock|null $entitySemaphoreLock
     * @param string $whereStmt
     * @param array $queryData
     * @param int $lockTimeout
     * @param float $lockCheckEvery
     * @param bool $autoReleaseLock
     * @param LockFlag|null $dbLockFlag
     * @return AbstractOrmEntity
     * @throws \Charcoal\App\Kernel\Orm\Exception\EntityNotFoundException
     * @throws \Charcoal\App\Kernel\Orm\Exception\EntityOrmException
     * @throws \Throwable
     */
    protected function getLockedEntity(
        ?AbstractLock &$entitySemaphoreLock,
        string        $whereStmt,
        array         $queryData = [],
        int           $lockTimeout = 0,
        float         $lockCheckEvery = 0.25,
        bool          $autoReleaseLock = true,
        ?LockFlag     $dbLockFlag = null,
    ): AbstractOrmEntity
    {
        if ($entitySemaphoreLock !== null) {
            throw new \LogicException(static::class . "::getLockedEntity() called with existing lock");
        }

        $lock = $this->module->getSemaphore()->obtainLock(
            $this->getEntityLockId($entity),
            $lockTimeout > 0 ? $lockCheckEvery : null,
            $lockTimeout
        );

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

            $entitySemaphoreLock = $lock;
            return $entity;
        } catch (\Throwable $t) {
            try {
                $lock->releaseLock();
            } catch (\Exception) {
            }

            throw $t;
        }
    }
}