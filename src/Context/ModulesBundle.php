<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Context;

use Charcoal\App\Kernel\Domain\AppAware;

/**
 * Class ModuleManifest
 * @package Charcoal\App\Kernel\Context
 * @internal
 */
final class ModulesBundle
{
    /** @var array<string,AppAware> */
    private array $modules = [];

    /**
     * @param \Closure(static): void $declareChildren
     */
    final public function __construct(\Closure $declareChildren)
    {
        $declareChildren($this);
    }

    /**
     * @param string $propertyKey
     * @param AppAware $module
     * @return $this
     */
    public function include(string $propertyKey, AppAware $module): self
    {
        $this->modules[$propertyKey] = $module;
        return $this;
    }

    /**
     * @return array<string, AppAware>
     */
    public function getIncluded(): array
    {
        return $this->modules;
    }
}