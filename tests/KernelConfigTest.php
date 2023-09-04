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

namespace Charcoal\Tests\Apps;

use Charcoal\Apps\Kernel\Config;
use Charcoal\Database\DbDriver;
use Charcoal\Yaml\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Class KernelConfigTest
 * @package Charcoal\Tests\Apps
 */
class KernelConfigTest extends TestCase
{
    /**
     * @return void
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     * @throws \Charcoal\Yaml\Exception\YamlParseException
     */
    public function testParseConfig1(): void
    {
        $parser = new Parser(evaluateBooleans: true, evaluateNulls: true);
        $config = new Config($parser->getParsed(__DIR__ . "/data/config1/config.yml"));

        // Main
        $this->assertEquals("Europe/London", $config->timezone);

        // Databases
        $primaryDb = $config->databases->get("primary");
        $this->assertEquals(DbDriver::MYSQL, $primaryDb->driver);
        $this->assertEquals("127.0.0.1", $primaryDb->host);
        $this->assertEquals(3306, $primaryDb->port);
        $this->assertEquals("primary", $primaryDb->dbName);
        $this->assertEquals("root", $primaryDb->username);
        $this->assertNull($primaryDb->password);

        $apiLogs = $config->databases->get("api_logs");
        $this->assertEquals(DbDriver::MYSQL, $apiLogs->driver);
        $this->assertEquals("127.0.0.2", $apiLogs->host);
        $this->assertEquals(3306, $apiLogs->port);
        $this->assertEquals("trash_db", $apiLogs->dbName);
        $this->assertEquals("root", $apiLogs->username);
        $this->assertEquals("P@s5w0rd#!!!_1", $apiLogs->password);

        // Cipher Keychain
        $this->assertEquals("a5b3f5001e39b1418908fb096d1bc120e8d8dc5c3d8e741d69d0bbdfd1f7fd9d", $config->security->keychain->get("primary")->toBase16());
        $this->assertEquals("f724e9468f7255f6c151d3b9a3591b1a3bc9f810f041d4e07f7fefc011925b5c", $config->security->keychain->get("another")->toBase16());
        $this->assertEquals("d1e3e544290e326f31ddd8d2643165f2319c47d73bc83dd13fd90b6875edef54", $config->security->keychain->get("more")->toBase16());
        $this->assertEquals("d1e3e544290e326f31ddd8d2643165f2319c47d73bc83dd13fd90b6875edef54", $config->security->keychain->get("more2")->toBase16());
        $this->assertEquals("f724e9468f7255f6c151d3b9a3591b1a3bc9f810f041d4e07f7fefc011925b5c", $config->security->keychain->get("another2")->toBase16());

        // Cache
        $this->assertTrue($config->cache->use);
        $this->assertEquals("redis", $config->cache->storageDriver);
        $this->assertEquals("127.0.0.1", $config->cache->hostname);
        $this->assertEquals(6379, $config->cache->port);
        $this->assertEquals(3, $config->cache->timeOut);
    }


    /**
     * @return void
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     */
    public function testInsecureEntropy(): void
    {
        $securityConfig = [
            "keychain" => [
                "primary" => "charcoal",
                "users" => "enter some random words or 32 bytes PRNG entropy (hex-encoded) here"
            ]
        ];

        $this->expectExceptionMessage('Insecure entropy for cipher key "users"');
        new Config\SecurityConfig($securityConfig);
    }
}

