<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\CacheStoreConfig;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;

/**
 * Class CacheConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 */
final class CacheConfigBuilder extends AbstractConfigCollector
{
    public function set(CacheStoreEnumInterface $key, CacheStoreConfig $config): void
    {
        $this->storeConfig($key, $config);
    }
}