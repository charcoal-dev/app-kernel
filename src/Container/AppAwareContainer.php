<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppBuild;

/**
 * Class AppAwareContainer
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAwareContainer extends AppAware
{
    /** @var string[] Map of instance property keys to be automatically serialized and bootstrapped */
    protected array $containerChildrenMap = [];

    /**
     * Ideally in child classes parent::__construct should be called near at end of their own constructors
     */
    protected function __construct()
    {
        // Determine children for this AppAwareContainer instance to be automatically serialized
        $reflect = new \ReflectionClass($this);
        foreach ($reflect->getProperties() as $property) {
            if ($property->isInitialized($this)) {
                if ($this->inspectIncludeChild($property->getValue($this))) {
                    $this->containerChildrenMap[] = $property->getName();
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
        foreach ($this->containerChildrenMap as $childPropertyKey) {
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
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = ["containerChildrenMap" => $this->containerChildrenMap];
        foreach ($this->containerChildrenMap as $child) {
            $data[$child] = $this->$child;
        }

        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    protected function onUnserialize(array $data): void
    {
        $this->containerChildrenMap = $data["containerChildrenMap"];
        foreach ($this->containerChildrenMap as $child) {
            $this->$child = $data[$child];
        }
    }
}