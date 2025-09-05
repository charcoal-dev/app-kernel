<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Module\OrmModuleBase;
use Charcoal\Database\DatabaseClient;

/**
 * Class OrmTableBase
 * @package Charcoal\App\Kernel\Orm\Db
 */
abstract class OrmTableBase extends \Charcoal\Database\Orm\AbstractOrmTable
{
    public readonly TableRegistryEnumInterface $enum;

    /**
     * @param OrmModuleBase $module
     * @param TableRegistryEnumInterface $dbTableEnum
     * @param class-string<OrmEntityBase>|null $entityClass
     */
    public function __construct(
        public readonly OrmModuleBase $module,
        TableRegistryEnumInterface    $dbTableEnum,
        public readonly ?string       $entityClass
    )
    {
        $this->enum = $dbTableEnum;
        parent::__construct($this->enum->getTableName(), $this->enum->getDriver());
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
     * @return OrmEntityBase|null
     */
    public function newChildObject(array $row): ?OrmEntityBase
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
     * @return DatabaseClient
     */
    public function getDb(): DatabaseClient
    {
        return $this->resolveDbInstance();
    }

    /**
     * @return DatabaseClient
     */
    protected function resolveDbInstance(): DatabaseClient
    {
        if ($this->dbInstance) {
            return $this->dbInstance;
        }

        $this->dbInstance = $this->module->app->database->getDb($this->enum->getDatabase());
        return $this->dbInstance;
    }
}