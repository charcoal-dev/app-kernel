<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Modules;

use Charcoal\App\Kernel\Build\AppBuildPartial;
use Charcoal\App\Kernel\Container\AppAwareContainer;

/**
 * Class BaseModule
 * @package Charcoal\App\Kernel\Modules
 */
class BaseModule extends AppAwareContainer
{
    /**
     * @param AppBuildPartial $app
     * @param \Closure $declareChildren
     */
    public function __construct(AppBuildPartial $app, \Closure $declareChildren)
    {
        parent::__construct($declareChildren, [$app]);
    }
}