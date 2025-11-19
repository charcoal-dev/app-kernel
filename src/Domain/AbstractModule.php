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
use Charcoal\Security\Secrets\Types\AbstractSecretKey;

/**
 * Class AbstractModule
 * @package Charcoal\App\Kernel\Domain
 */
abstract class AbstractModule implements AppBindableInterface, AppBootstrappableInterface
{
    use ControlledSerializableTrait;

    public readonly AbstractApp $app;
    public readonly ?ModuleSecurityBindings $security;

    private ?AbstractSecretKey $secretKey = null;

    /** @var string[] property keys to be automatically serialized and bootstrapped */
    private array $moduleChildren = [];

    protected function __construct()
    {
        if ($this instanceof RuntimeCacheOwnerInterface) {
            $this->initializePrivateRuntimeCache();
        }

        $this->security = $this->declareSecurityBindings();
    }

    /**
     * Resolves and returns the secret key configured in ModuleSecurityBindings
     * @api
     */
    public function getSecretKey(): ?AbstractSecretKey
    {
        if ($this->secretKey) {
            return $this->secretKey;
        }

        if ($this->security) {
            if ($this->security->secretKeyEnum) {
                $this->secretKey = $this->app->security->secrets->resolveSecretEnum($this->security->secretKeyEnum);
                return $this->secretKey;
            }
        }

        return null;
    }

    /**
     * @return ModuleSecurityBindings|null
     */
    abstract protected function declareSecurityBindings(): ?ModuleSecurityBindings;

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
        $data["security"] = null;
        $data["secretKey"] = null;
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

        $this->security = $this->declareSecurityBindings();
        $this->moduleChildren = $data["moduleChildren"];
        $this->secretKey = null;
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