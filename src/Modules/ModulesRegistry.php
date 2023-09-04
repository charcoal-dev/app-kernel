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

use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class Modules
 * @package Charcoal\Apps\Kernel\Modules
 */
class ModulesRegistry extends AbstractInstanceRegistry
{
    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param string $key
     * @param \Charcoal\Apps\Kernel\Modules\AbstractModule $module
     * @return void
     */
    public function register(string $key, AbstractModule $module): void
    {
        $this->registrySet($key, $module);
    }

    /**
     * @param string $key
     * @return \Charcoal\Apps\Kernel\Modules\AbstractModule
     */
    public function get(string $key): AbstractModule
    {
        if (!isset($this->instances[$key])) {
            throw new \LogicException(sprintf('App module "%s" was not registered', $key));
        }

        return $this->instances[$key];
    }
}
