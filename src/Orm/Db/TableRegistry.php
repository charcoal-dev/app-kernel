<?php
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
     * @param AbstractOrmTable $table
     * @return void
     */
    public function register(AbstractOrmTable $table): void
    {
        $db = $table->enum->getDatabase()->getDatabaseKey();
        $table = $table->enum->getTableName();
        if (!isset($this->map[$db][$table])) {
            $this->map[$db][$table] = $table;
        }
    }

    /**
     * Resolves argument Database and Table to retrieve AbstractOrmTable instance
     * @param TableRegistryEnumInterface $dbTable
     * @return AbstractOrmTable
     */
    public function resolve(TableRegistryEnumInterface $dbTable): AbstractOrmTable
    {
        $found = $this->map[$dbTable->getDatabase()->getDatabaseKey()][$dbTable->getTableName()] ?? null;
        if (!$found) {
            throw new \OutOfBoundsException(
                "Database \"{$dbTable->getDatabase()->getDatabaseKey()}\" " .
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
        return $this->map[$db->getDatabaseKey()] ?? [];
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