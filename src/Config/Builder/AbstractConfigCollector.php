<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Contracts\Config\ConfigCollectorInterface;
use Charcoal\App\Kernel\Contracts\Enums\ConfigEnumInterface;
use Charcoal\Base\Support\Helpers\ObjectHelper;

/**
 * Class AbstractConfigCollector
 * @package Charcoal\App\Kernel\Config\Builder
 */
abstract class AbstractConfigCollector implements ConfigCollectorInterface
{
    private array $configs = [];

    /**
     * @param ConfigEnumInterface $key
     * @param object $config
     * @return void
     */
    protected function storeConfig(ConfigEnumInterface $key, object $config): void
    {
        if (isset($this->configs[$key->getConfigKey()])) {
            throw new \RuntimeException("Config key '{$key->getConfigKey()}' for " .
                ObjectHelper::baseClassName(static::class) . " already exists");
        }

        if (!(new \ReflectionClass($config))->isReadOnly()) {
            throw new \RuntimeException(ObjectHelper::baseClassName($config::class) .
                " is not read-only; Cannot store in " . ObjectHelper::baseClassName(static::class));
        }

        $this->configs[$key->getConfigKey()] = $config;
    }

    /**
     * @param ConfigEnumInterface $key
     * @return bool
     * @api
     */
    protected function hasConfig(ConfigEnumInterface $key): bool
    {
        return isset($this->configs[$key->getConfigKey()]);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->configs);
    }

    /**
     * @return array
     */
    public function getCollection(): array
    {
        return $this->configs;
    }
}