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

use Charcoal\Apps\Kernel\Db\AbstractAppTable;
use Charcoal\Apps\Kernel\Modules\AbstractModule;
use Charcoal\Apps\Kernel\Modules\Components\AbstractOrmComponent;

/**
 * Class UsersComponent
 * @package Charcoal\Tests\Apps\Objects
 */
class UsersComponent extends AbstractOrmComponent
{
    public function __construct(AbstractModule $module, AbstractAppTable $table)
    {
        parent::__construct($module, $table);
    }

    /**
     * @param int $id
     * @return \Charcoal\Tests\Apps\Objects\User|null
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     */
    public function findById(int $id): ?User
    {
        /** @var \Charcoal\Tests\Apps\Objects\User */
        return $this->getObject("users_id:" . $id, "id", $id, false);
    }

    /**
     * @param string $username
     * @return \Charcoal\Tests\Apps\Objects\User|null
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     */
    public function findByUsername(string $username): ?User
    {
        /** @var \Charcoal\Tests\Apps\Objects\User */
        return $this->getObject("users_username:" . $username, "username", $username, false);
    }
}
