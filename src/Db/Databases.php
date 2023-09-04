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

namespace Charcoal\Apps\Kernel\Db;

use Charcoal\Apps\Kernel\AppKernel;
use Charcoal\Database\DbDriver;
use Charcoal\OOP\DependencyInjection\AbstractDIResolver;
use Charcoal\OOP\Traits\NoDumpTrait;

/**
 * Class Databases
 * @package Charcoal\Apps\Kernel
 */
class Databases extends AbstractDIResolver
{
    protected readonly ?string $mysqlRootPassword;

    use NoDumpTrait;

    /**
     * @param \Charcoal\Apps\Kernel\AppKernel $aK
     */
    public function __construct(private readonly AppKernel $aK)
    {
        parent::__construct(null);
        $mysqlRootPassword = trim(strval(getenv("MYSQL_ROOT_PASSWORD")));
        $this->mysqlRootPassword = $mysqlRootPassword ?: null;
    }

    /**
     * @return null[]|string[]
     */
    public function __serialize(): array
    {
        return ["mysqlRootPassword" => $this->mysqlRootPassword];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->mysqlRootPassword = $data["mysqlRootPassword"];
    }

    /**
     * @param string $key
     * @param array $args
     * @return \Charcoal\Apps\Kernel\Db\AppDatabase
     * @throws \Charcoal\Database\Exception\DbConnectionException
     */
    protected function resolve(string $key, array $args): AppDatabase
    {
        $cred = $this->aK->config->databases->get($key);
        if ($cred->driver === DbDriver::MYSQL) {
            if ($cred->username === "root" && !$cred->password) {
                $cred->password = $this->mysqlRootPassword;
            }
        }

        return new AppDatabase($cred);
    }

    /**
     * @return \Charcoal\Apps\Kernel\Db\AppDatabase
     */
    public function primary(): AppDatabase
    {
        return $this->getOrResolve("primary");
    }

    /**
     * @param string $key
     * @return \Charcoal\Apps\Kernel\Db\AppDatabase
     */
    public function getDb(string $key): AppDatabase
    {
        return $this->getOrResolve($key);
    }
}
