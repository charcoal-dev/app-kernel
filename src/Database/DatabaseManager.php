<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Database;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\Base\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Database\Database;
use Charcoal\Database\DbDriver;

/**
 * Class Databases
 * @package Charcoal\App\Kernel
 * @template-extends AbstractFactoryRegistry<Database>
 */
class DatabaseManager extends AbstractFactoryRegistry
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
     * Resolves DbCredentials object from config and, creates a Database instance
     * @param string $key
     * @return Database
     * @throws \Charcoal\Database\Exception\DbConnectionException
     * @throws \Throwable
     */
    protected function create(string $key): Database
    {
        $cred = $this->app->config->database->get($key);
        if ($cred->driver === DbDriver::MYSQL) {
            if ($cred->username === "root" && !$cred->password) {
                $cred->password = $this->app->config->database->mysqlRootPassword;
            }
        }

        $db = new Database($cred);
        $this->app->events->onDbConnection()->trigger([$db]);
        return $db;
    }

    /**
     * Gets existing Database instance or resolves it from configuration
     * @param DatabaseEnumInterface $key
     * @return Database
     */
    public function getDb(DatabaseEnumInterface $key): Database
    {
        return $this->getExistingOrCreate($key->getDatabaseKey());
    }

    /**
     * Returns aggregated (executed) queries logged in runtime memory from ALL databases
     * @return array
     */
    public function getAllQueries(): array
    {
        $queries = [];
        foreach ($this->instances as $dbTag => $dbInstance) {
            foreach ($dbInstance->queries as $dbQuery) {
                $queries[] = [
                    "db" => $dbTag,
                    "query" => $dbQuery
                ];
            }
        }

        return $queries;
    }

    /**
     * Flushes all stored (executed) queries from runtime memory from ALL databases
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