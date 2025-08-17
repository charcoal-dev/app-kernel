<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Database;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\Base\Abstracts\BaseFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Database\DatabaseClient;

/**
 * Class Databases
 * @package Charcoal\App\Kernel
 * @template-extends BaseFactoryRegistry<DatabaseClient>
 */
class DatabaseManager extends BaseFactoryRegistry
{
    protected readonly AbstractApp $app;
    public readonly TableRegistry $tables;

    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct()
    {
        $this->tables = new TableRegistry();
    }

    /**
     * @param AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
    }

    /**
     * Prepares class for serializing,
     * Removes all current Database instances
     * @return array
     */
    public function __serialize(): array
    {
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
        $this->tables = $data["tables"];
        $this->instances = [];
    }

    /**
     * @param string $key
     * @return DatabaseClient
     * @throws \Charcoal\Database\Exceptions\DbConnectionException
     */
    protected function create(string $key): DatabaseClient
    {
        $config = $this->app->config->database?->databases[$key];
        if (!$config) {
            throw new \DomainException("Database config not found for key: " . $key);
        }

        // Todo: resolve secret as per reference

        return new DatabaseClient($config);
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
     * @return array
     */
    public function getAllQueries(): array
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
    public function flushAllQueries(): int
    {
        $flushed = 0;
        foreach ($this->instances as $db) {
            $flushed += $db->queries->count();
            $db->queries->flush();
        }

        return $flushed;
    }
}