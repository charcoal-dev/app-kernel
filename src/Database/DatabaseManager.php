<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Database;

use Charcoal\App\Kernel\Config\Snapshot\DatabaseManagerConfig;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\Base\Abstracts\BaseFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Traits\ControlledSerializableTrait;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Database\DatabaseClient;

/**
 * Class Databases
 * @package Charcoal\App\Kernel
 * @template-extends BaseFactoryRegistry<DatabaseClient>
 */
class DatabaseManager extends BaseFactoryRegistry implements AppServiceInterface
{
    public readonly TableRegistry $tables;

    use ControlledSerializableTrait;
    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct(public readonly ?DatabaseManagerConfig $config)
    {
        $this->tables = new TableRegistry();
    }

    /**
     * @param DatabaseEnumInterface $key
     * @return DatabaseClient
     */
    public function getDb(DatabaseEnumInterface $key): DatabaseClient
    {
        return $this->getExistingOrCreate($key->getConfigKey());
    }

    /**
     * @param string $key
     * @return DatabaseClient
     * @throws \Charcoal\Database\Exceptions\DbConnectionException
     */
    protected function create(string $key): DatabaseClient
    {
        $config = $this->config?->databases[$key];
        if (!$config) {
            throw new \DomainException("Database config not found for key: " . $key);
        }

        // Todo: resolve secret as per reference

        return new DatabaseClient($config);
    }

    /**
     * Prepares class for serializing,
     * Removes all current Database instances
     * @return array
     */
    public function collectSerializableData(): array
    {
        $data["config"] = $this->config;
        $data["tables"] = $this->tables;
        $data["instances"] = null;
        return $data;
    }

    /**
     * Resets class to fresh on unserialize
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->config = $data["config"];
        $this->tables = $data["tables"];
        $this->instances = [];
    }

    /**
     * @return array
     */
    public function queriesAggregate(): array
    {
        $queries = [];
        foreach ($this->instances as $dbId => $dbInstance) {
            foreach ($dbInstance->queries as $dbQuery) {
                $queries[] = [
                    "db" => $dbId,
                    "query" => $dbQuery
                ];
            }
        }

        return $queries;
    }

    /**
     * @return int
     */
    public function queriesFlush(): int
    {
        $flushed = 0;
        foreach ($this->instances as $db) {
            $flushed += $db->queries->count();
            $db->queries->flush();
        }

        return $flushed;
    }

    public static function unserializeDependencies(): array
    {
        return [static::class, DatabaseManagerConfig::class];
    }
}