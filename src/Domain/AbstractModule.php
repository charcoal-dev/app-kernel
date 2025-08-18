<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Domain;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;

/**
 * Class AbstractModule
 * @package Charcoal\App\Kernel\Domain
 */
abstract class AbstractModule extends AbstractAppAware
{
    /** @var string[] property keys to be automatically serialized and bootstrapped */
    private array $containerChildren = [];

    /**
     * @throws \ReflectionException
     */
    protected function __construct()
    {
        if ($this instanceof RuntimeCacheOwnerInterface) {
            $this->initializePrivateRuntimeCache();
        }

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
     * @param mixed $value
     * @return bool
     */
    protected function inspectIncludeChild(mixed $value): bool
    {
        return $value instanceof AbstractAppAware;
    }

    /**
     * Bootstrap itself and all AppAware children
     * @param AbstractApp $app
     * @return void
     */
    final public function bootstrap(AbstractApp $app): void
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
        if ($this->$childPropertyKey instanceof AbstractAppAware) {
            $this->$childPropertyKey->bootstrap($this->app);
        }
    }

    /**
     * @return array[]|\string[][]
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
        if ($this instanceof RuntimeCacheOwnerInterface) {
            $this->initializePrivateRuntimeCache();
        }

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