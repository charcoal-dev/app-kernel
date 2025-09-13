<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Db;

use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Objects\ObjectHelper;
use Charcoal\Contracts\Dataset\Sort;

/**
 * Fetch paginated entities from a table.
 */
final readonly class FetchPaginatedEntities
{
    public int $totalCount;
    public array $entities;
    public int $count;

    /**
     * @throws WrappedException
     */
    public function __construct(
        OrmTableBase $table,
        array        $whereQuery,
        array        $whereData,
        public Sort  $sortFlag,
        string       $sortColumn = "id",
        public int   $page = 1,
        public int   $perPage = 100,
        ?\Closure    $forEachRow = null,
    )
    {
        if ($this->page < 1 || $this->perPage < 1) {
            throw new \InvalidArgumentException("Invalid pagination arguments");
        }

        $whereQuery = $whereQuery ? implode(" AND ", $whereQuery) : "1";
        $this->totalCount = $this->getTotalRowCount($table, $whereQuery, $whereData);
        if (!$this->totalCount) {
            $this->entities = [];
            $this->count = 0;
            return;
        }

        try {
            $entities = $table->queryFind(
                $whereQuery,
                $whereData,
                null,
                $sortFlag,
                $sortColumn,
                offset: ($this->page * $this->perPage) - $this->perPage,
                limit: $this->perPage,
            )->getAll();
        } catch (\Exception $e) {
            throw new WrappedException($e, "Failed to retrieve paginated entities for table: " .
                ObjectHelper::baseClassName($table::class));
        }

        $result = [];
        foreach ($entities as $entity) {
            if ($forEachRow) {
                $entity = $forEachRow($entity);
                if ($entity) {
                    $result[] = $entity;
                }

                continue;
            }

            $result[] = $entity;
        }

        $this->entities = $result;
        $this->count = count($result);
    }

    /**
     * @throws WrappedException
     */
    private function getTotalRowCount(OrmTableBase $table, string $whereQuery, array $whereData): int
    {
        try {
            $totalCount = $table->getDb()
                ->fetch(sprintf("SELECT count(*) FROM `%s` WHERE %s", $table->name, $whereQuery), $whereData)
                ->getNext();
            $totalCount = intval($totalCount["count(*)"] ?? -1);
            if ($totalCount < 0) {
                throw new \RuntimeException("Cannot read count(*)");
            }

            return $totalCount;
        } catch (\Exception $e) {
            throw new WrappedException($e, "Failed to retrieve total row count for table: " .
                ObjectHelper::baseClassName($table::class));
        }
    }
}