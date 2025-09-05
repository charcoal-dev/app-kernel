<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Domain;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Domain\AppBindableInterface;
use Charcoal\App\Kernel\Contracts\Domain\AppBootstrappableInterface;
use Charcoal\Base\Objects\ObjectHelper;

/**
 * Represents a bundle of domain-specific modules within the application.
 * The class is responsible for managing modules, constructing them, and providing methods to
 * bootstrap and retrieve these modules. Each module must implement the AppBindableInterface.
 */
final readonly class DomainBundle implements AppBootstrappableInterface
{
    /** @var array<string, AppBindableInterface> */
    private array $modules;
    /** @var array<string, class-string<AppBindableInterface>> */
    private array $map;

    /**
     * @param array<array<\UnitEnum, callable(AbstractApp): AppBindableInterface>> $modules
     * @internal
     */
    public function __construct(AbstractApp $app, array $modules)
    {
        $resolved = [];
        $map = [];
        foreach ($modules as $module) {
            $name = $module[0] ?? null;
            $callable = $module[1] ?? null;
            if (!$name instanceof \UnitEnum || !is_callable($module[1])) {
                throw new \DomainException("Invalid module configuration");
            }

            if (isset($this->modules[$name->name])) {
                throw new \DomainException(sprintf('Module "%s" already registered', $name->name));
            }

            try {
                $bindable = $callable($app);
            } catch (\Throwable $t) {
                throw new \DomainException(
                    sprintf('Caught "%s" while constructing "%s" module', $t::class, $name->name),
                    previous: $t
                );
            }

            if (!$bindable instanceof AppBindableInterface) {
                throw new \DomainException(sprintf('Expected module "%s" to return an instance of "%s", got "%s"',
                    $name->name,
                    ObjectHelper::baseClassName(AppBindableInterface::class),
                    is_object($bindable) ? get_class($bindable) : gettype($bindable)
                ));
            }

            $resolved[$name->name] = $bindable;
            $map[$name->name] = get_class($bindable);
        }

        $this->modules = $resolved;
        $this->map = $map;
    }

    /**
     * @param AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        foreach ($this->modules as $module) {
            if ($module instanceof AppBootstrappableInterface) {
                $module->bootstrap($app);
            }
        }
    }

    /**
     * @return array<string, class-string<AppBindableInterface>>
     */
    public function inspect(): array
    {
        return $this->map;
    }

    /**
     * @param \UnitEnum $module
     * @return AppBindableInterface
     */
    public function get(\UnitEnum $module): AppBindableInterface
    {
        if (!isset($this->modules[$module->name])) {
            throw new \DomainException(sprintf('Module "%s" not registered', $module->name));
        }

        return $this->modules[$module->name];
    }
}