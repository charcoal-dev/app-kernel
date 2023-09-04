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

namespace Charcoal\Apps\Kernel\Config;

use Charcoal\Apps\Kernel\Exception\AppConfigException;
use Charcoal\Database\DbCredentials;
use Charcoal\Database\DbDriver;
use Charcoal\OOP\Traits\NoDumpTrait;

/**
 * Class DbConfig
 * @package Charcoal\Apps\Kernel\Config
 */
class DbConfig
{
    /** @var array */
    private array $dbs = [];

    use NoDumpTrait;

    /**
     * @param mixed $config
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     */
    public function __construct(mixed $config)
    {
        if (!is_array($config)) {
            throw new AppConfigException('Invalid databases configuration');
        }

        foreach ($config as $label => $dbConfig) {
            if (!preg_match('/^\w{2,16}$/', $label)) {
                throw new AppConfigException('Invalid label for database object in YML');
            }

            $driver = DbDriver::tryFrom(strval($dbConfig["driver"] ?? ""));
            if (!$driver) {
                throw new AppConfigException('Unsupported or invalid database driver');
            }

            $hostname = $dbConfig["host"] ?? null;
            if (!is_string($hostname) || !filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                throw new AppConfigException(sprintf('Invalid database "%s" server IPv4 address', $label));
            }

            $port = intval($dbConfig["port"] ?? 0);
            if ($port <= 0x3e8 || $port >= 0xffff) {
                throw new AppConfigException(sprintf('Invalid database "%s" server port', $label));
            }

            $name = $dbConfig["name"] ?? null;
            if (!is_string($name) || !preg_match('/^[\w.\-]{3,32}$/', $name)) {
                throw new AppConfigException(sprintf('Invalid database "%s" dbname', $label));
            }

            $username = $dbConfig["username"] ?? null;
            if (!is_string($username) && !is_null($username)) {
                throw new AppConfigException(sprintf('Invalid database "%s" username', $label));
            }

            $password = $dbConfig["password"] ?? null;
            if (!is_string($password) && !is_null($password)) {
                throw new AppConfigException(sprintf('Invalid database "%s" password', $label));
            }

            $this->dbs[$label] = new DbCredentials($driver, $name, $hostname, $port, $username, $password, false);
        }
    }

    /**
     * @param string $key
     * @return \Charcoal\Database\DbCredentials
     */
    public function get(string $key): DbCredentials
    {
        if (!isset($this->dbs[$key])) {
            throw new \OutOfRangeException(sprintf('No database configured matching "%s" key', $key));
        }

        return $this->dbs[$key];
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->dbs;
    }
}
