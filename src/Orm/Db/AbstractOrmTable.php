<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;
use Charcoal\App\Kernel\Orm\AbstractOrmModule;
use Charcoal\App\Kernel\Orm\Repository\AbstractOrmEntity;
use Charcoal\Database\Database;

/**
 * Class AbstractOrmTable
 * @package Charcoal\App\Kernel\Orm\Db
 */
abstract class AbstractOrmTable extends \Charcoal\Database\ORM\AbstractOrmTable
{
    public readonly TableRegistryEnumInterface $enum;

    public function __construct(
        public readonly AbstractOrmModule $module,
        TableRegistryEnumInterface        $dbTableEnum,
        public readonly ?string           $entityClass
    )
    {
        $this->enum = $dbTableEnum;
        parent::__construct($this->enum->getTableName());
    }

    /**
     * @param int|string $uniqueId
     * @return string
     */
    public function suggestEntityId(int|string $uniqueId): string
    {
        return $this->name . ":" . $uniqueId;
    }

    /**
     * @param array $row
     * @return AbstractOrmEntity|null
     */
    public function newChildObject(array $row): ?AbstractOrmEntity
    {
        $entityClass = $this->entityClass;
        if (!$entityClass) {
            return null;
        }

        return new $entityClass();
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["module"] = $this->module;
        $data["enum"] = $this->enum;
        $data["entityClass"] = $this->entityClass;
        return $data;
    }

    /**
     * @param array $object
     * @return void
     */
    public function __unserialize(array $object): void
    {
        $this->module = $object["module"];
        $this->enum = $object["enum"];
        $this->entityClass = $object["entityClass"];
        parent::__unserialize($object);
    }

    /**
     * @return Database
     */
    public function getDb(): Database
    {
        return $this->resolveDbInstance(null);
    }

    /**
     * @param Database|null $dbArg
     * @return Database
     */
    protected function resolveDbInstance(?Database $dbArg = null): Database
    {
        if ($dbArg) {
            return $dbArg;
        }

        if ($this->dbInstance) {
            return $this->dbInstance;
        }

        $this->dbInstance = $this->module->app->databases->getDb($this->enum->getDatabase()->getDatabaseKey());
        return $this->dbInstance;
    }
}