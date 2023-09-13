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

namespace Charcoal\Apps\Kernel\Modules\Components;

use Charcoal\Apps\Kernel\Modules\BaseModule;

/**
 * Class BaseComponent
 * @package Charcoal\Apps\Kernel\Modules\Components
 */
abstract class BaseComponent
{
    /**
     * @param \Charcoal\Apps\Kernel\Modules\BaseModule $module
     */
    public function __construct(
        public readonly BaseModule $module
    )
    {
    }

    /**
     * @return \Charcoal\Apps\Kernel\Modules\BaseModule[]
     */
    public function __serialize(): array
    {
        return ["module" => $this->module];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->module = $data["module"];
    }
}
