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

use Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface;
use Charcoal\Apps\Kernel\Errors\NullErrorLogger;
use Charcoal\Apps\Kernel\Polyfill\NullCache;
use Charcoal\Cache\Cache;
use Charcoal\Cache\Drivers\RedisClient;
use Charcoal\Filesystem\Directory;
use Charcoal\Semaphore\FilesystemSemaphore;

/**
 * Class AppKernel
 * @package Charcoal\Apps\Kernel
 */
class AppKernel
{
    public readonly Errors $errors;
    public readonly Config $config;
    public readonly Directories $dir;
    public readonly Databases $db;
    public readonly Cache $cache;
    public readonly FilesystemSemaphore $semaphore;

    /**
     * @param \Charcoal\Apps\Kernel\Lifecycle $lifecycle
     * @param \Charcoal\Filesystem\Directory $rootDirectory
     * @param \Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface $errorLogger
     * @param string $configClass
     * @param string $dirClass
     * @param string $dbClass
     * @throws \Charcoal\Filesystem\Exception\FilesystemException
     * @throws \Charcoal\Semaphore\Exception\SemaphoreException
     */
    public function __construct(
        public readonly Lifecycle $lifecycle,
        Directory                 $rootDirectory,
        ErrorLoggerInterface      $errorLogger = new NullErrorLogger(),
        string                    $configClass = Config::class,
        string                    $dirClass = Directories::class,
        string                    $dbClass = Databases::class,
    )
    {
        $this->errors = new Errors($this, $errorLogger);
        $this->config = new $configClass();
        $this->dir = new $dirClass($rootDirectory);
        $this->db = new $dbClass($this);
        $this->semaphore = new FilesystemSemaphore($this->dir->semaphore);

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
            "config" => $this->config,
            "dir" => $this->dir,
            "db" => $this->db,
            "cache" => null,
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
        $this->config = $data["config"];
        $this->dir = $data["dir"];
        $this->db = $data["db"];
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

        // All set!
        $this->errors->exceptionHandlerShowTrace = false;
        $this->lifecycle->log("AppKernelInitialized");
    }
}