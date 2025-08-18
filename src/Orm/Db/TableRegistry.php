<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;

/**
 * Class TableRegistry
 * @package Charcoal\App\Kernel\Database
 */
class TableRegistry
{
    protected array $map = [];

    /**
     * Registers an AbstractOrmTable instance in DatabaseTableRegistry
     * @param OrmTableBase $table
     * @return void
     */
    public function register(OrmTableBase $table): void
    {
        $db = $table->enum->getDatabase()->getConfigKey();
        $tableId = $table->enum->getTableName();
        if (!isset($this->map[$db][$tableId])) {
            $this->map[$db][$tableId] = $table;
        }
    }

    /**
     * Resolves argument Database and Table to retrieve OrmTableBase instance
     * @param TableRegistryEnumInterface $dbTable
     * @return OrmTableBase
     */
    public function resolve(TableRegistryEnumInterface $dbTable): OrmTableBase
    {
        $found = $this->map[$dbTable->getDatabase()->getConfigKey()][$dbTable->getTableName()] ?? null;
        if (!$found) {
            throw new \OutOfBoundsException(
                "Database \"{$dbTable->getDatabase()->getConfigKey()}\" " .
                "table \"{$dbTable->getTableName()}\" not found in registry"
            );
        }

        return $found;
    }

    /**
     * Returns all declared tables under a DB
     * @param DatabaseEnumInterface $db
     * @return array
     */
    public function getDatabaseTables(DatabaseEnumInterface $db): array
    {
        return $this->map[$db->getConfigKey()] ?? [];
    }

    /**
     * Returns entire collection of databases and their tables
     * @return array
     */
    public function getCollection(): array
    {
        return $this->map;
    }
}