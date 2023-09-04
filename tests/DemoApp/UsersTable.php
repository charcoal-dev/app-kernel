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

namespace Charcoal\Tests\Apps\Objects;

use Charcoal\Apps\Kernel\Db\AbstractAppTable;
use Charcoal\Database\ORM\Schema\Charset;
use Charcoal\Database\ORM\Schema\Columns;
use Charcoal\Database\ORM\Schema\Constraints;
use Charcoal\Database\ORM\Schema\TableMigrations;

/**
 * Class UsersTable
 * @package Charcoal\Tests\Apps\Objects
 */
class UsersTable extends AbstractAppTable
{
    public const TABLE = "users";

    protected function structure(Columns $cols, Constraints $constraints): void
    {
        $cols->setDefaultCharset(Charset::ASCII);

        $cols->int("id")->bytes(4)->unSigned()->autoIncrement();
        $cols->enum("status")->options("active", "frozen", "disabled")->default("active");
        $cols->binaryFrame("checksum")->fixed(20);
        $cols->string("username")->length(16)->unique();
        $cols->string("email")->length(32)->unique();
        $cols->string("first_name")->charset(Charset::UTF8MB4)->length(32)->isNullable();
        $cols->string("last_name")->charset(Charset::UTF8MB4)->length(32)->isNullable();
        $cols->string("country")->fixed(3)->isNullable();
        $cols->int("joined_on")->bytes(4)->unSigned();
        $cols->setPrimaryKey("id");
    }

    protected function migrations(TableMigrations $migrations): void
    {
    }

    public function newModelObject(array $row): object|null
    {
        return new User();
    }
}
