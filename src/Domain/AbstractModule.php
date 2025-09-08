<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Domain;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Cache\RuntimeCacheOwnerInterface;
use Charcoal\App\Kernel\Contracts\Domain\AppBindableInterface;
use Charcoal\App\Kernel\Contracts\Domain\AppBootstrappableInterface;
use Charcoal\App\Kernel\Contracts\Domain\ModuleBindableInterface;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;

/**
 * Class AbstractModule
 * @package Charcoal\App\Kernel\Domain
 */
abstract class AbstractModule implements AppBindableInterface, AppBootstrappableInterface
{
    use ControlledSerializableTrait;

    public readonly AbstractApp $app;

    /** @var string[] property keys to be automatically serialized and bootstrapped */
    private array $moduleChildren = [];

    protected function __construct()
    {
        if ($this instanceof RuntimeCacheOwnerInterface) {
            $this->initializePrivateRuntimeCache();
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function inspectIncludeChild(mixed $value): bool
    {
        return $value instanceof ModuleBindableInterface;
    }

    /**
     * Bootstrap itself and all AppAware children
     * @param AbstractApp $app
     * @return void
     */
    final public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;

        // Determine children for this AbstractModule instance to be automatically serialized
        $reflect = new \ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if ($property->isInitialized($this)) {
                if ($this->inspectIncludeChild($property->getValue($this))) {
                    $this->moduleChildren[] = $property->getName();
                }
            }
        }

        foreach ($this->moduleChildren as $childPropertyKey) {
            if (isset($this->$childPropertyKey) && is_object($this->$childPropertyKey)) {
                $this->bootstrapChildren($this->$childPropertyKey);
            }
        }
    }

    /**
     * @param object $childObject
     * @return void
     */
    protected function bootstrapChildren(object $childObject): void
    {
        if ($childObject instanceof AppBootstrappableInterface) {
            $childObject->bootstrap($this->app);
        }

        if ($childObject instanceof ModuleBindableInterface) {
            $childObject->bootstrap($this);
        }
    }

    /**
     * @return array[]|\string[][]
     */
    protected function collectSerializableData(): array
    {
        $data = ["moduleChildren" => $this->moduleChildren];
        foreach ($this->moduleChildren as $child) {
            $data[$child] = $this->$child;
        }

        $data["app"] = null;
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

        $this->moduleChildren = $data["moduleChildren"];
        foreach ($this->moduleChildren as $child) {
            if (!isset($this->$child) && isset($data[$child])) {
                $this->$child = $data[$child];
            }
        }
    }

    /**
     * @return array<string>
     * @api
     */
    public function getModuleComponents(): array
    {
        return $this->moduleChildren;
    }
}