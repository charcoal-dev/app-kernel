<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Entity\ChecksumAwareEntityInterface;
use Charcoal\App\Kernel\Contracts\Orm\Repository\ChecksumAwareRepositoryInterface;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Exceptions\EntityRepositoryException;
use Charcoal\Vectors\Strings\StringVector;

/**
 * Trait EntityUpdatableTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityUpdatableTrait
{
    /**
     * @param OrmEntityBase $entity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @return int
     * @throws EntityRepositoryException
     */
    protected function dbUpdateEntity(
        OrmEntityBase $entity,
        StringVector  $changeLog,
        int|string    $primaryColumnValue,
        string        $primaryColumnName = "id",
    ): int
    {
        $changeData = $this->extractChangeLogData($entity, $changeLog);
        return $this->dbUpdateRow($changeData, $primaryColumnValue, $primaryColumnName);
    }

    /**
     * @param OrmEntityBase $entity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @param string $checksumColumn
     * @return int
     * @throws EntityRepositoryException
     * @throws \Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException
     */
    protected function dbUpdateChecksumAwareEntity(
        OrmEntityBase $entity,
        StringVector  $changeLog,
        int|string    $primaryColumnValue,
        string        $primaryColumnName = "id",
        string        $checksumColumn = "checksum",
    ): int
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if (!$entity instanceof ChecksumAwareEntityInterface) {
            throw new \RuntimeException(static::class . " does not implement ChecksumAwareEntityInterface");
        }

        // Todo: resolve
        /** @noinspection PhpInstanceofIsAlwaysTrueInspection */
        if (!$this instanceof ChecksumAwareRepositoryInterface) {
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
     * @throws EntityRepositoryException
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
            throw new EntityRepositoryException($this, $t);
        }

        return $update->rowsCount;
    }

    /**
     * @param OrmEntityBase $entity
     * @param StringVector $changeLog
     * @return array
     */
    private function extractChangeLogData(
        OrmEntityBase $entity,
        StringVector  $changeLog
    ): array
    {
        $changes = $changeLog->filterUnique()->getArray();
        if ($changeLog->count() === 0) {
            throw new \InvalidArgumentException("No changes to update");
        }

        return $entity->extract(...$changes);
    }
}