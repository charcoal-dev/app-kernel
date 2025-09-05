<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Exceptions\EntityRepositoryException;
use Charcoal\Vectors\Strings\StringVector;

/**
 * Trait EntityUpsertTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityUpsertTrait
{
    /**
     * @param OrmEntityBase|array $entity
     * @param StringVector $variableColumnNames
     * @return int
     * @throws EntityRepositoryException
     */
    protected function dbUpsertEntity(
        OrmEntityBase|array $entity,
        StringVector        $variableColumnNames,
    ): int
    {
        try {
            return $this->table->querySave($entity, $variableColumnNames)->rowsCount;
        } catch (\Throwable $t) {
            throw new EntityRepositoryException($this, $t);
        }
    }
}