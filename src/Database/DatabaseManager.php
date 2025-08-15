<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Database;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\Base\Abstracts\BaseFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Database\DatabaseClient;
use Charcoal\Database\Enums\DbDriver;

/**
 * Class Databases
 * @package Charcoal\App\Kernel
 * @template-extends BaseFactoryRegistry<DatabaseClient>
 */
class DatabaseManager extends BaseFactoryRegistry
{
    protected readonly AppBuild $app;
    public readonly TableRegistry $tables;

    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct()
    {
        $this->tables = new TableRegistry();
    }

    /**
     * @param AppBuild $app
     * @return void
     */
    public function bootstrap(AppBuild $app): void
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
     * @throws \Charcoal\Database\Exception\DbConnectionException
     */
    protected function create(string $key): DatabaseClient
    {
        $cred = $this->app->config->database->get($key);
        if ($cred->driver === DbDriver::MYSQL) {
            if ($cred->username === "root" && !$cred->password) {
                $cred->password = $this->app->config->database->mysqlRootPassword;
            }
        }

        $db = new DatabaseClient($cred);
        $this->app->events->onDbConnection()->trigger([$db]);
        return $db;
    }

    /**
     * @param DatabaseEnumInterface $key
     * @return DatabaseClient
     */
    public function getDb(DatabaseEnumInterface $key): DatabaseClient
    {
        return $this->getExistingOrCreate($key->getDatabaseKey());
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