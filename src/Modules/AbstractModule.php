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

/**
 * Class AbstractModule
 * @package Charcoal\Apps\Kernel\Modules
 */
abstract class AbstractModule
{
    public readonly AbstractApp $app;
    public readonly ObjectsRegistry $objectsRegistry;
    public readonly ComponentsRegistry $components;

    public function __construct()
    {
        $this->objectsRegistry = new ObjectsRegistry($this);
        $this->components = new ComponentsRegistry();
    }

    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
    }

    /**
     * @return \Charcoal\Apps\Kernel\Modules\ComponentsRegistry[]
     */
    public function __serialize(): array
    {
        return [
            "components" => $this->components
        ];
    }

    /**
     * @param array $object
     * @return void
     */
    public function __unserialize(array $object): void
    {
        $this->components = $object["components"];
        $this->objectsRegistry = new ObjectsRegistry($this);
    }
}
