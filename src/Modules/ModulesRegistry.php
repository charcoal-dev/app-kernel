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

use Charcoal\Apps\Kernel\AbstractApp;
use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class ModulesRegistry
 * @package Charcoal\Apps\Kernel\Modules
 */
class ModulesRegistry extends AbstractInstanceRegistry implements \IteratorAggregate
{
    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param string $key
     * @param \Charcoal\Apps\Kernel\Modules\BaseModule $module
     * @return void
     */
    public function register(string $key, BaseModule $module): void
    {
        $this->registrySet($key, $module);
    }

    /**
     * @param string $key
     * @return \Charcoal\Apps\Kernel\Modules\BaseModule
     */
    public function get(string $key): BaseModule
    {
        if (!isset($this->instances[$key])) {
            throw new \LogicException(sprintf('App module "%s" was not registered', $key));
        }

        return $this->instances[$key];
    }

    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        /** @var \Charcoal\Apps\Kernel\Modules\AbstractOrmModule $instance */
        foreach ($this->instances as $instance) {
            $instance->bootstrap($app);
        }
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->instances);
    }
}
