<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Domain\AppBindableInterface;
use Charcoal\Base\Support\Helpers\ObjectHelper;

/**
 * Class DomainBundle
 * @package Charcoal\App\Kernel\Internal
 */
final class DomainBundle
{
    private array $modules = [];

    /**
     * @param array<array<\UnitEnum, callable(AbstractApp): AppBindableInterface>> $modules
     * @internal
     */
    public function __construct(AbstractApp $app, array $modules)
    {
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

            $this->modules[$name->name] = $bindable;
        }
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