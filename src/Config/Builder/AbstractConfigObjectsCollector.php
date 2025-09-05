<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Internal\Config\ConfigEnumInterface;
use Charcoal\Base\Objects\ObjectHelper;

/**
 * Class AbstractConfigObjectsCollector
 * @package Charcoal\App\Kernel\Config\Builder
 * @template TKey of ConfigEnumInterface
 * @template TConfig of object
 * @template TCompiled of object
 */
abstract class AbstractConfigObjectsCollector
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

    /**
     * @return TCompiled
     * @api
     */
    abstract public function build(): object;
}