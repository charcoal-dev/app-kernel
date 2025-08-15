<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Orm\Entity\AbstractOrmEntity;
use Charcoal\App\Kernel\Orm\Exception\EntityOrmException;
use Charcoal\Base\Support\Helpers\ObjectHelper;

/**
 * Trait EntityInsertableTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait EntityInsertableTrait
{
    /**
     * @param AbstractOrmEntity $object
     * @return void
     * @throws EntityOrmException
     */
    protected function dbInsert(AbstractOrmEntity $object): void
    {
        try {
            $insertOp = $this->table->queryInsert($object, false);
        } catch (\Throwable $t) {
            throw new EntityOrmException(static::class, $t);
        }

        if ($insertOp->rowsCount !== 1) {
            throw new \RuntimeException('Failed to insert a row in ' . ObjectHelper::baseClassName(static::class));
        }
    }

    /**
     * @param AbstractOrmEntity $object
     * @param string $idColumn
     * @param int|null $overrideId
     * @return void
     * @throws EntityOrmException
     */
    protected function dbInsertAndSetId(AbstractOrmEntity $object, string $idColumn = "id", ?int $overrideId = null): void
    {
        if (isset($object->$idColumn)) {
            throw new \LogicException('Cannot insert ' . ObjectHelper::baseClassName($object::class) .
                ' while its property $' . $idColumn . ' is already set');
        }

        $object->$idColumn = $overrideId ?: 0;
        $this->dbInsert($object);

        try {
            $object->$idColumn = $this->table->getDb()->lastInsertId();
        } catch (\Throwable $t) {
            throw new EntityOrmException(static::class, $t);
        }
    }
}