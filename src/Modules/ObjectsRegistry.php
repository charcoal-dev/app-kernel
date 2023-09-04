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

namespace Charcoal\Apps\Kernel\Modules;

use Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject;
use Charcoal\Cache\CachedReferenceKey;
use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class ObjectsRegistry
 * @package Charcoal\Apps\Kernel\Modules
 */
class ObjectsRegistry extends AbstractInstanceRegistry
{
    /**
     * @param \Charcoal\Apps\Kernel\Modules\AbstractModule $module
     */
    public function __construct(public readonly AbstractModule $module)
    {
        parent::__construct(null);
    }

    /**
     * @param string $registryKey
     * @return \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject|null
     */
    public function get(string $registryKey): ?AbstractAppObject
    {
        $registryKey = strtolower($registryKey);
        if (isset($this->instances[$registryKey])) {
            return $this->instances[$registryKey];
        }

        return null;
    }

    /**
     * @param string $cacheKey
     * @return \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject|null
     * @throws \Charcoal\Cache\Exception\CacheException
     */
    public function getFromCache(string $cacheKey): ?AbstractAppObject
    {
        $object = $this->module->app->kernel->cache->get(strtolower($cacheKey));
        if ($object instanceof CachedReferenceKey) {
            $object = $object->resolve($this->module->app->kernel->cache);
        }

        return $object instanceof AbstractAppObject ? $object : null;
    }

    /**
     * @param \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject $object
     * @return void
     */
    public function store(AbstractAppObject $object): void
    {
        $bindingKeys = $object->getRegistryKeys();
        foreach ($bindingKeys as $bindingKey) {
            $this->registrySet(strtolower($bindingKey), $object);
        }
    }

    /**
     * @param \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject $object
     * @return void
     * @throws \Charcoal\Cache\Exception\CacheException
     */
    public function storeInCache(AbstractAppObject $object): void
    {
        $bindingKeys = $object->getRegistryKeys();
        $primaryKey = array_shift($bindingKeys);
        $this->module->app->kernel->cache->set(strtolower($primaryKey), $object);

        if ($bindingKeys) {
            foreach ($bindingKeys as $referenceKey) {
                $this->module->app->kernel->cache->createReferenceKey(strtolower($referenceKey), $primaryKey);
            }
        }
    }

    /**
     * @return array
     */
    public function getAllRuntime(): array
    {
        return $this->instances;
    }
}
