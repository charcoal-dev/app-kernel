<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

use Charcoal\App\Kernel\Container\AppAware;

/**
 * Class BuildModules
 * @package Charcoal\App\Kernel
 */
class BuildPlan
{
    private array $plan = [];

    /**
     * @param \Closure $declareChildren
     */
    final public function __construct(\Closure $declareChildren)
    {
        $declareChildren($this);
    }

    /**
     * This method should be called from inside Closure provided to constructor of this class
     * @param string $propertyKey
     * @param AppAware $module
     * @return void
     */
    public function include(string $propertyKey, AppAware $module): void
    {
        $this->plan[$propertyKey] = $module;
    }

    /**
     * Returns build plan, it is called from inside AppKernel constructor
     * @return array
     */
    public function getPlan(): array
    {
        return $this->plan;
    }
}