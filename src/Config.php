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

namespace Charcoal\Apps\Kernel;

use Charcoal\Apps\Kernel\Config\CacheConfig;
use Charcoal\Apps\Kernel\Config\DbConfig;
use Charcoal\Apps\Kernel\Config\SecurityConfig;
use Charcoal\Apps\Kernel\Exception\AppConfigException;

/**
 * Class Config
 * @package Charcoal\Apps\Kernel
 */
class Config
{
    public readonly DbConfig $databases;
    public readonly CacheConfig $cache;
    public readonly SecurityConfig $security;

    public readonly string $domain;
    public readonly bool $https;
    public readonly string $timezone;

    /**
     * @param array $config
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     */
    public function __construct(array $config)
    {
        $this->databases = new DbConfig($config["databases"] ?? null);
        $this->cache = new CacheConfig($config["cache"] ?? null);
        $this->security = new SecurityConfig($config["security"] ?? null);

        $domain = $config["domain"] ?? null;
        if (!is_string($domain) || !preg_match('/^[a-z\d\-]+(\.[a-z\d\-]+)*$/', $domain)) {
            throw new AppConfigException('Invalid domain name');
        }

        $https = $config["https"] ?? null;
        if (!is_bool($https)) {
            throw new AppConfigException('Invalid type for "https" boolean');
        }

        $timezone = $config["timezone"] ?? null;
        if (!is_string($timezone) || !in_array($timezone, \DateTimeZone::listIdentifiers())) {
            throw new AppConfigException('Invalid timezone');
        }

        $this->timezone = $timezone;
        $this->domain = $domain;
        $this->https = $https;
    }
}
