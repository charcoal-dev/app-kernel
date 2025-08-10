<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Repository\ChecksumAwareInterface;
use Charcoal\App\Kernel\Orm\Entity\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Entity\LockedEntity;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\OOP\OOP;
use Charcoal\OOP\Vectors\StringVector;

/**
 * Trait EntityUpdatableTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityUpdatableTrait
{
    /**
     * @param string $entityClass
     * @return \LogicException
     */
    protected function dbUpdateOutOfScopeException(string $entityClass): \LogicException
    {
        return new \LogicException("Out of scope; Cannot update " .
            OOP::baseClassName($entityClass) . " from " . OOP::baseClassName(static::class));
    }

    /**
     * @param bool $isChecksumAware
     * @param LockedEntity $lockedEntity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @return void
     * @throws EntityOrmException
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException
     */
    protected function dbUpdateLockedEntity(
        bool         $isChecksumAware,
        LockedEntity $lockedEntity,
        StringVector $changeLog,
        int|string   $primaryColumnValue,
        string       $primaryColumnName = "id",
    ): void
    {
        if ($isChecksumAware) {
            $this->dbUpdateChecksumAwareEntity(
                $lockedEntity->entity,
                $changeLog,
                $primaryColumnValue,
                $primaryColumnName
            );
        } else {
            $this->dbUpdateEntity(
                $lockedEntity->entity,
                $changeLog,
                $primaryColumnValue,
                $primaryColumnName
            );
        }
    }

    /**
     * @param AbstractOrmEntity $entity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @return int
     * @throws EntityOrmException
     */
    protected function dbUpdateEntity(
        AbstractOrmEntity $entity,
        StringVector      $changeLog,
        int|string        $primaryColumnValue,
        string            $primaryColumnName = "id",
    ): int
    {
        $changeData = $this->extractChangeLogData($entity, $changeLog);
        return $this->dbUpdateRow($changeData, $primaryColumnValue, $primaryColumnName);
    }

    /**
     * @param AbstractOrmEntity $entity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @param string $checksumColumn
     * @return int
     * @throws EntityOrmException
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException
     */
    protected function dbUpdateChecksumAwareEntity(
        AbstractOrmEntity $entity,
        StringVector      $changeLog,
        int|string        $primaryColumnValue,
        string            $primaryColumnName = "id",
        string            $checksumColumn = "checksum",
    ): int
    {
        if (!$this instanceof ChecksumAwareInterface) {
            throw new \RuntimeException(static::class . " does not implement ChecksumAwareRepositoryInterface");
        }

        $changeData = $this->extractChangeLogData($entity, $changeLog);
        $changeData[$checksumColumn] = $this->calculateChecksum($entity);
        return $this->dbUpdateRow($changeData, $primaryColumnValue, $primaryColumnName);
    }

    /**
     * @param array $changeData
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @return int
     * @throws EntityOrmException
     */
    private function dbUpdateRow(
        array      $changeData,
        int|string $primaryColumnValue,
        string     $primaryColumnName = "id"
    ): int
    {
        try {
            $update = $this->table->queryUpdate($changeData, $primaryColumnValue, $primaryColumnName);
        } catch (\Throwable $t) {
            throw new EntityOrmException(static::class, $t);
        }

        return $update->rowsCount;
    }

    /**
     * @param AbstractOrmEntity $entity
     * @param StringVector $changeLog
     * @return array
     */
    private function extractChangeLogData(
        AbstractOrmEntity $entity,
        StringVector      $changeLog
    ): array
    {
        $changes = $changeLog->filterUnique()->getArray();
        if ($changeLog->count() === 0) {
            throw new \InvalidArgumentException("No changes to update");
        }

        $changeData = [];
        $entity->extractValues($changeData, ...$changes);
        return $changeData;
    }
}