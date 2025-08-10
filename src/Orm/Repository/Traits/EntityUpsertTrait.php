<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Orm\Entity\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\OOP\Vectors\StringVector;

/**
 * Trait EntityUpsertTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityUpsertTrait
{
    /**
     * @param AbstractOrmEntity|array $entity
     * @param StringVector $variableColumnNames
     * @return int
     * @throws EntityOrmException
     */
    protected function dbUpsertEntity(
        AbstractOrmEntity|array $entity,
        StringVector            $variableColumnNames,
    ): int
    {
        try {
            return $this->table->querySave($entity, $variableColumnNames)->rowsCount;
        } catch (\Throwable $t) {
            throw new EntityOrmException(static::class, $t);
        }
    }
}