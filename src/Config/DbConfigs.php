<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

use Charcoal\App\Kernel\Orm\Db\DatabaseEnum;
use Charcoal\Database\DbCredentials;
use Charcoal\OOP\Traits\NoDumpTrait;

/**
 * Class DbConfigs
 * @package Charcoal\App\Kernel\Config
 */
class DbConfigs
{
    private array $databases = [];

    use NoDumpTrait;

    /**
     * @param string|null $mysqlRootPassword
     */
    public function __construct(
        #[\SensitiveParameter]
        public readonly ?string $mysqlRootPassword = null,
    )
    {
    }

    /**
     * Adds a database configuration available to AppKernel
     * @param DatabaseEnum|string $key
     * @param DbCredentials $dbConfig
     * @return void
     */
    public function set(DatabaseEnum|string $key, DbCredentials $dbConfig): void
    {
        $key = $key instanceof DatabaseEnum ? $key->getDatabaseKey() : $key;
        $this->databases[$key] = $dbConfig;
    }

    /**
     * Returns a specific database configuration or throws \OutOfRangeException if none matched
     * @param DatabaseEnum|string $key
     * @return DbCredentials
     */
    public function get(DatabaseEnum|string $key): DbCredentials
    {
        $key = $key instanceof DatabaseEnum ? $key->getDatabaseKey() : $key;
        if (!isset($this->dbs[$key])) {
            throw new \OutOfRangeException(sprintf('No database configured matching "%s" key', $key));
        }

        return $this->dbs[$key];
    }

    /**
     * Returns all database configurations
     * @return array
     */
    public function getAll(): array
    {
        return $this->databases;
    }
}