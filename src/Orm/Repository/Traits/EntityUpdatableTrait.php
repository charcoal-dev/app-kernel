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
use Charcoal\App\Kernel\Orm\Entity\LockedEntity;
use Charcoal\App\Kernel\Orm\Exceptions\EntityRepositoryException;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Base\Vectors\StringVector;

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
            ObjectHelper::baseClassName($entityClass) . " from " . ObjectHelper::baseClassName(static::class));
    }

    /**
     * @param bool $isChecksumAware
     * @param LockedEntity $lockedEntity
     * @param StringVector $changeLog
     * @param int|string $primaryColumnValue
     * @param string $primaryColumnName
     * @return void
     * @throws EntityRepositoryException
     * @throws \Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException
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