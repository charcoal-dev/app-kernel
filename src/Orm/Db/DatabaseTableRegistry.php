<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

/**
 * Class DatabaseTableRegistry
 * @package Charcoal\App\Kernel\Orm\Db
 */
class DatabaseTableRegistry
{
    protected array $map = [];

    /**
     * Registers an AbstractOrmTable instance in DatabaseTableRegistry
     * @param AbstractOrmTable $table
     * @return void
     */
    public function register(AbstractOrmTable $table): void
    {
        if (!isset($this->map[$table->enum->getDatabase()->getDatabaseKey()][$table->enum->getTableName()])) {
            $this->map[$table->enum->getDatabase()->getDatabaseKey()][$table->enum->getTableName()] = $table;
        }
    }

    /**
     * Resolves argument Database and Table to retrieve AbstractOrmTable instance
     * @param DbAwareTableEnum $dbTable
     * @return AbstractOrmTable
     */
    public function resolve(DbAwareTableEnum $dbTable): AbstractOrmTable
    {
        $found = $this->map[$dbTable->getDatabase()->getDatabaseKey()][$dbTable->getTableName()] ?? null;
        if (!$found) {
            throw new \OutOfBoundsException(
                "ORM database \"{$dbTable->getDatabase()->getDatabaseKey()}\" table \"{$dbTable->getTableName()}\" not found in registry"
            );
        }

        return $found;
    }

    /**
     * Returns all declared tables under a DB
     * @param DatabaseEnum $db
     * @return array
     */
    public function getDatabaseTables(DatabaseEnum $db): array
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