<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Modules;

use Charcoal\Apps\Kernel\Db\AbstractAppTable;

/**
 * Class TablesRegistry
 * @package Charcoal\Apps\Kernel\Modules
 */
class TablesRegistry
{
    private array $dbTables = [];

    /**
     * @param string $dbInstanceId
     * @param \Charcoal\Apps\Kernel\Db\AbstractAppTable $tableInstance
     * @return void
     */
    public function register(string $dbInstanceId, AbstractAppTable $tableInstance): void
    {
        if (!array_key_exists($dbInstanceId, $this->dbTables)) {
            $this->dbTables[$dbInstanceId] = [];
        }

        $this->dbTables[$dbInstanceId][] = $tableInstance;
    }

    /**
     * @param string $dbInstanceId
     * @return array
     */
    public function getFor(string $dbInstanceId): array
    {
        return $this->dbTables[$dbInstanceId] ?? [];
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->dbTables;
    }
}
