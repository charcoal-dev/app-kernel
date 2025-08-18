<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Sandbox\TestApp;

use Charcoal\App\Kernel\AppManifest;
use Charcoal\Tests\App\Fixtures\Enums\ModuleIndex;
use Charcoal\Tests\App\Fixtures\Orm\Example\ExampleModule;

class TestAppFactory extends AppManifest
{
    public function __construct()
    {
        $this->bind(ModuleIndex::example, fn(TestApp $app) => self::ExampleModule($app));
    }

    public static function ExampleModule(TestApp $app): ExampleModule
    {
        return new ExampleModule($app);
    }
}