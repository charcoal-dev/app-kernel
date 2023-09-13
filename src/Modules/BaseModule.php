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
abstract class BaseModule
{
    public readonly AbstractApp $app;

    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }
}
