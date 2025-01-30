<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Container\AppAwareInterface;
use Charcoal\App\Kernel\Orm\Db\DatabaseTableRegistry;
use Charcoal\Database\Database;
use Charcoal\Database\DbDriver;
use Charcoal\OOP\DependencyInjection\AbstractDIResolver;
use Charcoal\OOP\Traits\NoDumpTrait;

/**
 * Class Databases
 * @package Charcoal\App\Kernel
 */
class Databases extends AbstractDIResolver implements AppAwareInterface
{
    protected readonly AppKernel $app;
    public readonly DatabaseTableRegistry $orm;

    use NoDumpTrait;

    /**
     * Constructor explicitly defined to expose "protected" constructor from parent
     */
    public function __construct()
    {
        parent::__construct(Database::class);
        $this->orm = new DatabaseTableRegistry();
    }

    /**
     * @param AppKernel $app
     * @return void
     */
    public function bootstrap(AppKernel $app): void
    {
        $this->app = $app;
    }

    /**
     * Prepares class for serialize,
     * Removes all current Database instances
     * @return array
     */
    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["instances"] = null;
        $data["orm"] = $this->orm;
        return $data;
    }

    /**
     * Resets class to fresh on unserialize
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->orm = $data["orm"];
        parent::__unserialize(["instanceOf" => $this->instanceOf, "instances" => []]);
    }

    /**
     * Resolves DbCredentials object from config and, creates a Database instance
     * @param string $key
     * @param array $args
     * @return Database
     * @throws \Charcoal\Database\Exception\DbConnectionException
     * @throws \Throwable
     */
    protected function resolve(string $key, array $args): Database
    {
        $cred = $this->app->config->databases->get($key);
        if ($cred->driver === DbDriver::MYSQL) {
            if ($cred->username === "root" && !$cred->password) {
                $cred->password = $this->app->config->databases->mysqlRootPassword;
            }
        }

        $db = new Database($cred);
        $this->app->events->onDbConnection()->trigger([$db]);
        return $db;
    }

    /**
     * Gets existing Database instance or resolves it from configuration
     * @param string $key
     * @return Database
     */
    public function getDb(string $key): Database
    {
        return $this->getOrResolve($key);
    }

    /**
     * Returns aggregated (executed) queries logged in runtime memory from ALL databases
     * @return array
     */
    public function getAllQueries(): array
    {
        $queries = [];

        /** @var Database $dbInstance */
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

        /** @var Database $db */
        foreach ($this->instances as $db) {
            $flushed += $db->queries->count();
            $db->queries->flush();
        }

        return $flushed;
    }
}