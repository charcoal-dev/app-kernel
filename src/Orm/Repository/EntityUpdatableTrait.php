<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\App\Kernel\Orm\Exception\NoChangesException;
use Charcoal\OOP\Vectors\StringVector;

/**
 * Trait EntityUpdatableTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityUpdatableTrait
{
    /**
     * @param AbstractOrmEntity $entity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @param bool $changesRequired
     * @return int
     * @throws EntityOrmException
     * @throws NoChangesException
     */
    protected function dbUpdateEntity(
        AbstractOrmEntity $entity,
        StringVector      $changeLog,
        int|string        $primaryColumnValue,
        string            $primaryColumnName = "id",
        bool              $changesRequired = true
    ): int
    {
        $changeData = $this->extractChangeLogData($entity, $changeLog, $changesRequired);
        return $this->dbUpdateRow($changeData, $primaryColumnValue, $primaryColumnName);
    }

    /**
     * @param AbstractOrmEntity $entity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @param string $checksumColumn
     * @param bool $changesRequired
     * @return int
     * @throws EntityOrmException
     * @throws NoChangesException
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException
     */
    protected function dbUpdateChecksumAwareEntity(
        AbstractOrmEntity $entity,
        StringVector      $changeLog,
        int|string        $primaryColumnValue,
        string            $primaryColumnName = "id",
        string            $checksumColumn = "checksum",
        bool              $changesRequired = true
    ): int
    {
        if (!$this instanceof ChecksumAwareRepositoryInterface) {
            throw new \RuntimeException(static::class . " does not implement ChecksumAwareRepositoryInterface");
        }

        $changeData = $this->extractChangeLogData($entity, $changeLog, $changesRequired);
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
     * @param bool $changesRequired
     * @return array
     * @throws NoChangesException
     */
    private function extractChangeLogData(
        AbstractOrmEntity $entity,
        StringVector      $changeLog,
        bool              $changesRequired = true
    ): array
    {
        $changes = $changeLog->filterUnique()->getArray();
        if ($changesRequired && $changeLog->count() === 0) {
            throw new NoChangesException($entity::class);
        }

        $changeData = [];
        $entity->extractValues($changeData, ...$changes);
        return $changeData;
    }
}