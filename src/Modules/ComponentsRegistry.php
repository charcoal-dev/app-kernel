<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Modules;

use Charcoal\Apps\Kernel\Modules\Components\AbstractComponent;
use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class ComponentsRegistry
 * @package Charcoal\Apps\Kernel\Modules
 */
class ComponentsRegistry extends AbstractInstanceRegistry
{
    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param string $key
     * @param \Charcoal\Apps\Kernel\Modules\Components\AbstractComponent $component
     * @return void
     */
    public function register(string $key, AbstractComponent $component): void
    {
        $this->registrySet($key, $component);
    }

    /**
     * @param string $key
     * @return \Charcoal\Apps\Kernel\Modules\Components\AbstractComponent
     */
    public function get(string $key): AbstractComponent
    {
        if (!isset($this->instances[$key])) {
            throw new \LogicException(sprintf('App module component "%s" was not registered', $key));
        }

        return $this->instances[$key];
    }
}
