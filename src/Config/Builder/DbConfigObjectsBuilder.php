<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\DatabaseConfig;
use Charcoal\App\Kernel\Config\Snapshot\DatabaseManagerConfig;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;

/**
 * Class DbConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 * @extends AbstractConfigObjectsCollector<DatabaseEnumInterface, DatabaseConfig, DatabaseManagerConfig>
 * @implements ConfigBuilderInterface<DatabaseManagerConfig>
 */
final class DbConfigObjectsBuilder extends AbstractConfigObjectsCollector implements ConfigBuilderInterface
{
    /**
     * @param DatabaseEnumInterface $key
     * @param DatabaseConfig $config
     * @return void
     */
    public function set(DatabaseEnumInterface $key, DatabaseConfig $config): void
    {
        $this->storeConfig($key, $config);
    }

    /**
     * @return DatabaseManagerConfig
     */
    public function build(): DatabaseManagerConfig
    {
        return new DatabaseManagerConfig($this->getCollection());
    }
}