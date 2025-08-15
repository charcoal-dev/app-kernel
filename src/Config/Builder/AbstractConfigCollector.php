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
 * @template TKey of ConfigEnumInterface
 * @template TConfig of object
 * @template-implements ConfigCollectorInterface<TConfig>
 */
abstract class AbstractConfigCollector implements ConfigCollectorInterface
{
    /** @var array<string, TConfig> */
    private array $configs = [];

    /**
     * @param TKey $key
     * @param TConfig $config
     * @return void
     */
    protected function storeConfig(ConfigEnumInterface $key, object $config): void
    {
        if (isset($this->configs[$key->getConfigKey()])) {
            throw new \RuntimeException("Config key '{$key->getConfigKey()}' for " .
                ObjectHelper::baseClassName(static::class) . " already exists");
        }

        $this->configs[$key->getConfigKey()] = $config;
    }

    /**
     * @param TKey $key
     * @return bool
     * @api
     */
    protected function hasConfig(ConfigEnumInterface $key): bool
    {
        return isset($this->configs[$key->getConfigKey()]);
    }

    /**
     * @param TKey $key
     * @return TConfig
     */
    public function getConfig(ConfigEnumInterface $key): object
    {
        if (!$this->hasConfig($key)) {
            throw new \RuntimeException("Config key '{$key->getConfigKey()}' for " .
                ObjectHelper::baseClassName(static::class) . " does not exist");
        }
        return $this->configs[$key->getConfigKey()];
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->configs);
    }

    /**
     * @return array<string, TConfig>
     */
    public function getCollection(): array
    {
        return $this->configs;
    }
}