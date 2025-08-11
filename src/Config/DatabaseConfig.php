<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Database\DbCredentials;

/**
 * Class DbConfigs
 * @package Charcoal\App\Kernel\Config
 */
class DatabaseConfig
{
    private array $database = [];

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
     * @param DatabaseEnumInterface|string $key
     * @param DbCredentials $dbConfig
     * @return void
     */
    public function set(DatabaseEnumInterface|string $key, DbCredentials $dbConfig): void
    {
        $key = $key instanceof DatabaseEnumInterface ? $key->getDatabaseKey() : $key;
        $this->database[$key] = $dbConfig;
    }

    /**
     * Returns a specific database configuration or throws \OutOfRangeException if none matched
     * @param DatabaseEnumInterface|string $key
     * @return DbCredentials
     */
    public function get(DatabaseEnumInterface|string $key): DbCredentials
    {
        $key = $key instanceof DatabaseEnumInterface ? $key->getDatabaseKey() : $key;
        if (!isset($this->database[$key])) {
            throw new \OutOfRangeException(sprintf('No database configured matching "%s" key', $key));
        }

        return $this->database[$key];
    }

    /**
     * Returns all database configurations
     * @return array
     */
    public function getAll(): array
    {
        return $this->database;
    }
}