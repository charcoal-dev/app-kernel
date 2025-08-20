<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Sandbox\TestApp;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\AppManifest;
use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\Filesystem\Path\DirectoryPath;

/**
 * Class TestApp
 * @package Charcoal\Tests\Kernel\TestApp
 */
class TestApp extends AbstractApp
{
    protected function resolveAppConfig(AppEnv $env, PathRegistry $paths): AppConfig
    {
        return ConfigProvider::getConfig($env, $paths);
    }

    protected function resolveAppManifest(): AppManifest
    {
        return new TestAppFactory();
    }
}