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

use Charcoal\Apps\Kernel\Modules\AbstractOrmModule;

class UsersModule extends AbstractOrmModule
{
    public readonly UsersComponent $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new UsersComponent($this);
    }

    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["users"] = $this->users;
        return $data;
    }

    public function __unserialize(array $data): void
    {
        $this->users = $data["users"];
        parent::__unserialize($data);
    }
}
