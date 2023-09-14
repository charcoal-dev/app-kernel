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

use Charcoal\Apps\Kernel\Modules\Components\AbstractOrmComponent;

/**
 * Class UsersComponent
 * @package Charcoal\Tests\Apps\Objects
 * @property \Charcoal\Tests\Apps\Objects\UsersModule $module
 * @property \Charcoal\Tests\Apps\Objects\UsersTable $table
 */
class UsersComponent extends AbstractOrmComponent
{
    public function __construct(UsersModule $module)
    {
        parent::__construct($module, new UsersTable($module));
    }

    /**
     * @param int $id
     * @return \Charcoal\Tests\Apps\Objects\User
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     */
    public function findById(int $id): User
    {
        /** @var \Charcoal\Tests\Apps\Objects\User */
        return $this->getObject("users_id:" . $id, $this->table, "id", $id, false);
    }

    /**
     * @param string $username
     * @return \Charcoal\Tests\Apps\Objects\User
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     */
    public function findByUsername(string $username): User
    {
        /** @var \Charcoal\Tests\Apps\Objects\User */
        return $this->getObject("users_username:" . $username, $this->table, "username", $username, false);
    }
}
