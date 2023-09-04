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

namespace Charcoal\Apps\Kernel\Db;

use Charcoal\Database\Database;
use Charcoal\Database\DbCredentials;

/**
 * Class AppDatabase
 * @package Charcoal\Apps\Kernel\Db
 */
class AppDatabase extends Database
{
    public readonly TablesRegistry $tables;

    /**
     * @param \Charcoal\Database\DbCredentials $credentials
     * @param int $errorMode
     * @throws \Charcoal\Database\Exception\DbConnectionException
     */
    public function __construct(DbCredentials $credentials, int $errorMode = \PDO::ERRMODE_EXCEPTION)
    {
        parent::__construct($credentials, $errorMode);
        $this->tables = new TablesRegistry();
    }
}