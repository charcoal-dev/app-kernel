<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Orm\Example;

use Charcoal\App\Kernel\Orm\Db\OrmTableBase;
use Charcoal\Base\Enums\Charset;
use Charcoal\Database\Orm\Schema\Columns;
use Charcoal\Database\Orm\Schema\Constraints;
use Charcoal\Database\Orm\Schema\TableMigrations;
use Charcoal\Tests\App\Fixtures\Enums\DbTables;

/**
 * Class ExampleTable
 * @package Charcoal\Tests\App\Fixtures\Orm\Example
 */
class ExampleTableBase extends OrmTableBase
{
    public function __construct(ExampleModule $module)
    {
        parent::__construct($module, DbTables::Example, ExampleEntity::class);
    }

    protected function structure(Columns $cols, Constraints $constraints): void
    {
        $cols->setDefaultCharset(Charset::ASCII);

        $cols->int("id")->bytes(4)->unSigned()->autoIncrement();
        $cols->string("username")->length(20)->unique();
        $cols->setPrimaryKey("id");

        $constraints->foreignKey("referrer_id")->table($this->enum->getTableName(), "id");
    }

    protected function migrations(TableMigrations $migrations): void
    {
    }
}