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

namespace Charcoal\Tests\Apps\Objects;

use Charcoal\Apps\Kernel\Modules\AbstractModule;

class UsersModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct();
        $this->components->register("users", new UsersComponent($this, new UsersTable($this, "primary")));
    }

    public function users(): UsersComponent
    {
        /** @var \Charcoal\Tests\Apps\Objects\UsersComponent */
        return $this->components->get("users");
    }
}
