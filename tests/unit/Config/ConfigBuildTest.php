<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Unit\Config;

use Charcoal\App\Kernel\Config\Builder\AbstractConfigObjectsCollector;
use Charcoal\App\Kernel\Config\Builder\CacheConfigObjectsBuilder;
use Charcoal\App\Kernel\Config\Builder\DbConfigObjectsBuilder;
use Charcoal\App\Kernel\Config\Snapshot\CacheManagerConfig;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigBuildTest
 * @package Charcoal\Tests\App\Unit
 */
class ConfigBuildTest extends TestCase
{
    /**
     * @return void
     */
    public function testCacheConfigObjectClass(): void
    {
        $rc = new \ReflectionClass(CacheConfigObjectsBuilder::class);
        $this->assertTrue($rc->isFinal(), 'CacheConfigObject must be final.');
        $this->assertTrue(
            $rc->isSubclassOf(AbstractConfigObjectsCollector::class),
            'CacheConfigObject must extend AbstractConfigCollector.'
        );
        $this->assertTrue(
            $rc->implementsInterface(ConfigBuilderInterface::class),
            'CacheConfigObject must implement ConfigCollectorInterface.'
        );

        $rc2 = new \ReflectionClass(CacheManagerConfig::class);
        $this->assertTrue($rc->isFinal(), 'CacheConfigObject must be final.');
        $this->assertTrue(
            $rc->isSubclassOf(AbstractConfigObjectsCollector::class),
            'CacheConfigObject must extend AbstractConfigCollector.'
        );
        $this->assertTrue(
            $rc->implementsInterface(ConfigBuilderInterface::class),
            'CacheConfigObject must implement ConfigCollectorInterface.'
        );
    }

    /**
     * @return void
     */
    public function testDbConfigBuilderClass(): void
    {
        $rc = new \ReflectionClass(DbConfigObjectsBuilder::class);
        $this->assertTrue($rc->isFinal(), 'DbConfigBuilder must be final.');
        $this->assertTrue(
            $rc->isSubclassOf(AbstractConfigObjectsCollector::class),
            'DbConfigBuilder must extend AbstractConfigCollector.'
        );
        $this->assertTrue(
            $rc->implementsInterface(ConfigBuilderInterface::class),
            'DbConfigBuilder must implement ConfigBuilderInterface.'
        );
    }
}