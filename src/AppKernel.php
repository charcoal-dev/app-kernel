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

use Charcoal\Apps\Kernel\Db\Databases;
use Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface;
use Charcoal\Apps\Kernel\Errors\NullErrorLogger;
use Charcoal\Apps\Kernel\Polyfill\NullCache;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CacheDriverInterface;
use Charcoal\Cache\Drivers\RedisClient;
use Charcoal\Filesystem\Directory;
use Charcoal\Semaphore\FilesystemSemaphore;
use Charcoal\Yaml\Parser;

/**
 * Class AppKernel
 * @package Charcoal\Apps\Kernel
 */
class AppKernel
{
    public readonly Events $events;
    public readonly Errors $errors;
    public readonly Cache $cache;
    public readonly Config $config;
    public readonly Directories $dir;
    public readonly Databases $db;
    public readonly FilesystemSemaphore $semaphore;

    /**
     * @param \Charcoal\Filesystem\Directory $rootDirectory
     * @param \Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface $errorLogger
     * @param string $configClass
     * @param string $dirClass
     * @param string $dbClass
     * @param string $eventsClass
     * @throws \Charcoal\Filesystem\Exception\FilesystemException
     * @throws \Charcoal\Semaphore\Exception\SemaphoreException
     * @throws \Charcoal\Yaml\Exception\YamlParseException
     */
    public function __construct(
        Directory            $rootDirectory,
        ErrorLoggerInterface $errorLogger = new NullErrorLogger(),
        string               $configClass = Config::class,
        string               $dirClass = Directories::class,
        string               $dbClass = Databases::class,
        string               $eventsClass = Events::class,
    )
    {
        $this->dir = new $dirClass($rootDirectory);
        $this->errors = new Errors($this, $errorLogger);
        $this->events = new $eventsClass();

        $configObject = (new Parser(evaluateBooleans: true, evaluateNulls: true))
            ->getParsed($this->dir->config->pathToChild("/config.yml", false));
        $this->config = new $configClass($configObject);
        $this->db = new $dbClass();
        $this->semaphore = new FilesystemSemaphore($this->dir->semaphore);


        // Cache Engine
        $cacheConfig = $this->config->cache;
        $cacheDriver = $cacheConfig->use && $cacheConfig->storageDriver === "redis" ?
            new RedisClient($cacheConfig->hostname, $cacheConfig->port, $cacheConfig->timeOut) : new NullCache();

        $this->cache = new Cache(
            $cacheDriver,
            useChecksumsByDefault: false,
            nullIfExpired: true,
            deleteIfExpired: true
        );

        // Actual runtime initialization happens here:
        $this->init();
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            "errors" => $this->errors,
            "events" => $this->events,
            "config" => $this->config,
            "dir" => $this->dir,
            "db" => $this->db,
            "cache" => $this->cache,
            "semaphore" => $this->semaphore,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->errors = $data["errors"];
        $this->events = $data["events"];
        $this->config = $data["config"];
        $this->dir = $data["dir"];
        $this->db = $data["db"];
        $this->cache = $data["cache"];
        $this->semaphore = $data["semaphore"];
        $this->init(); // Runtime initialization
    }

    /**
     * @return void
     */
    private function init(): void
    {
        // Timezone
        date_default_timezone_set($this->config->timezone);

        // Databases
        $this->db->bootstrap($this);

        // Cache Events
        $this->cache->events->onConnected()->listen(function (CacheDriverInterface $cacheDriver) {
            $this->events->onCacheConnection()->trigger([$cacheDriver]);
        });

        // All set!
        $this->errors->exceptionHandlerShowTrace = false;
    }
}