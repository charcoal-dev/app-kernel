<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;
use Charcoal\App\Kernel\Domain\DomainBundle;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Module\OrmModuleBase;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Database\DatabaseClient;

/**
 * Class OrmTableBase
 * @package Charcoal\App\Kernel\Orm\Db
 */
abstract class OrmTableBase extends \Charcoal\Database\Orm\AbstractOrmTable
{
    use ControlledSerializableTrait;

    public readonly TableRegistryEnumInterface $enum;
    public readonly OrmModuleBase $module;
    private readonly string $moduleFqcn;

    /**
     * @param OrmModuleBase $module
     * @param TableRegistryEnumInterface $dbTableEnum
     * @param class-string<OrmEntityBase>|null $entityClass
     */
    public function __construct(
        OrmModuleBase              $module,
        TableRegistryEnumInterface $dbTableEnum,
        public readonly ?string    $entityClass
    )
    {
        $this->enum = $dbTableEnum;
        $this->module = $module;
        $this->moduleFqcn = $module::class;
        parent::__construct($this->enum->getTableName(), $this->enum->getDriver());
    }

    /**
     * @api
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
    protected function collectSerializableData(): array
    {
        $data = [];
        $data["module"] = $this->moduleFqcn;
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
        $this->enum = $object["enum"];
        $this->entityClass = $object["entityClass"];
        $this->moduleFqcn = $object["module"];
        parent::__unserialize($object);
    }

    /**
     * @param DomainBundle $modules
     * @return void
     * @noinspection PhpSecondWriteToReadonlyPropertyInspection
     */
    public function bootstrap(DomainBundle $modules): void
    {
        $this->module = $modules->searchFqcn($this->moduleFqcn);
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