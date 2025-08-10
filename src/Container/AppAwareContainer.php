<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Contracts\Container\CacheStoreAwareInterface;
use Charcoal\App\Kernel\Contracts\Container\RuntimeCacheOwnerInterface;

/**
 * Class AppAwareContainer
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAwareContainer extends AppAware
{
    /** @var string[] property keys to be automatically serialized and bootstrapped */
    private array $containerChildren = [];

    public readonly bool $hasPrivateRuntimeCache;
    public readonly bool $isCacheStoreAware;

    protected function __construct()
    {
        $this->initializeContracts();

        // Determine children for this AppAwareContainer instance to be automatically serialized
        $reflect = new \ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if ($property->isInitialized($this)) {
                if ($this->inspectIncludeChild($property->getValue($this))) {
                    $this->containerChildren[] = $property->getName();
                }
            }
        }
    }

    /**
     * @return void
     */
    public function initializeContracts(): void
    {
        $this->isCacheStoreAware = $this instanceof CacheStoreAwareInterface ?
            $this->initializeCacheStoreAwareContainer() : false;

        $this->hasPrivateRuntimeCache = $this instanceof RuntimeCacheOwnerInterface ?
            $this->initializePrivateRuntimeCache() : false;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function inspectIncludeChild(mixed $value): bool
    {
        return $value instanceof AppAware;
    }

    /**
     * Bootstrap itself and all AppAware children
     * @param AppBuild $app
     * @return void
     */
    final public function bootstrap(AppBuild $app): void
    {
        parent::bootstrap($app);
        foreach ($this->containerChildren as $childPropertyKey) {
            if (isset($this->$childPropertyKey)) {
                $this->bootstrapChildren($childPropertyKey);
            }
        }
    }

    /**
     * @param string $childPropertyKey
     * @return void
     */
    protected function bootstrapChildren(string $childPropertyKey): void
    {
        if ($this->$childPropertyKey instanceof AppAware) {
            $this->$childPropertyKey->bootstrap($this->app);
        }
    }

    /**
     * Deliberately does not include "hasPrivateRuntimeCache", "isCacheStoreAware",
     * and other contract trait declared properties
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = ["containerChildren" => $this->containerChildren];
        foreach ($this->containerChildren as $child) {
            $data[$child] = $this->$child;
        }

        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->initializeContracts();
        $this->containerChildren = $data["containerChildren"];
        foreach ($this->containerChildren as $child) {
            $this->$child = $data[$child];
        }
    }

    /**
     * @return array|string[]
     */
    public function getContainerChildren(): array
    {
        return $this->containerChildren;
    }
}