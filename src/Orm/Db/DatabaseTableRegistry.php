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
        if (!isset($this->map[$table->database->getDatabaseKey()][$table->table->getTableName()])) {
            $this->map[$table->database->getDatabaseKey()][$table->table->getTableName()] = $table;
        }
    }

    /**
     * Resolves argument Database and Table to retrieve AbstractOrmTable instance
     * @param DatabaseEnum $db
     * @param TableNameEnum $table
     * @return AbstractOrmTable
     */
    public function resolve(DatabaseEnum $db, TableNameEnum $table): AbstractOrmTable
    {
        $found = $this->map[$db->getDatabaseKey()][$table->getTableName()] ?? null;
        if (!$found) {
            throw new \OutOfBoundsException(
                "ORM database \"{$db->getDatabaseKey()}\" table \"{$table->getTableName()}\" not found in registry"
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