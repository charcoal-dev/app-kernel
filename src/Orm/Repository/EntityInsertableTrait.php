<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\OOP\OOP;

/**
 * Trait EntityInsertableTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityInsertableTrait
{
    /**
     * @param AbstractOrmEntity $object
     * @param string $idColumn
     * @param int|null $overrideId
     * @return void
     * @throws EntityOrmException
     */
    protected function insertAndSetId(AbstractOrmEntity $object, string $idColumn = "id", ?int $overrideId = null): void
    {
        if (isset($object->$idColumn)) {
            throw new \LogicException('Cannot insert ' . OOP::baseClassName($object::class) .
                ' while its property $' . $idColumn . ' is already set');
        }

        try {
            $object->$idColumn = $overrideId ?: 0;
            $insertOp = $this->table->queryInsert($object, false);
        } catch (\Throwable $t) {
            throw new EntityOrmException($object::class, $t);
        }

        if ($insertOp->rowsCount !== 1) {
            throw new \RuntimeException('Failed to insert a row in ' . OOP::baseClassName(static::class));
        }

        try {
            $object->$idColumn = $this->table->getDb()->lastInsertId();
        } catch (\Throwable $t) {
            throw new EntityOrmException($object::class, $t);
        }
    }
}