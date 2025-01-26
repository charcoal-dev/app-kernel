<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Build\AppBuildCache;
use Charcoal\App\Kernel\Build\AppBuildEnum;
use Charcoal\App\Kernel\Build\BuildMetadata;
use Charcoal\App\Kernel\Config\CacheDriver;
use Charcoal\App\Kernel\Container\AppAware;
use Charcoal\App\Kernel\Errors\ErrorHandler;
use Charcoal\App\Kernel\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Polyfill\NullErrorLog;
use Charcoal\Cache\Cache;
use Charcoal\Cache\CacheDriverInterface;
use Charcoal\Filesystem\Directory;

/**
 * Class AppKernel
 * @package Charcoal\App\Kernel
 */
abstract class AppKernel extends AppBuildCache
{
    public readonly BuildMetadata $build;
    public readonly Cache $cache;
    public readonly Config $config;
    public readonly Databases $databases;
    public readonly Directories $directories;
    public readonly ErrorHandler $errors;
    public readonly Events $events;
    public readonly Lifecycle $lifecycle;

    public function __construct(
        AppBuildEnum         $build,
        Directory            $rootDirectory,
        ErrorLoggerInterface $errorLog = new NullErrorLog(),
        string               $directoriesClass = Directories::class,
        string               $eventsClass = Events::class,
        string               $databasesClass = Databases::class,
    )
    {
        $this->directories = new $directoriesClass($rootDirectory);
        $this->errors = new ErrorHandler($this, $build, $errorLog);
        $this->events = new $eventsClass();

        // Configuration should be rendered after ErrorHandler initialized...
        $this->config = $this->renderConfig();

        // Initialize rest of components...
        $this->databases = new $databasesClass();
        $this->cache = new Cache(
            CacheDriver::CreateClient($this->config->cache),
            useChecksumsByDefault: false,
            nullIfExpired: true,
            deleteIfExpired: true
        );

        // Build Modules
        $modules = $build->getBuildPlan()->getPlan();
        $modulesClasses = [];
        $modulesProperties = [];
        foreach ($modules as $property => $instance) {
            $this->$property = $instance;
            $modulesClasses[$instance::class] = $property;
            $modulesProperties[] = $property;
        }

        $this->isReady("New app instantiated");

        // Created after isReady call because of timestamp:
        $this->build = new BuildMetadata($build, time(), $modulesClasses, $modulesProperties);
    }

    /**
     * This method must return Config object for AppKernel after initializing error handlers
     * @return Config
     */
    abstract protected function renderConfig(): Config;

    /**
     * Returns AppAware module or service that was included in build plan
     * @param string $className
     * @return AppAware
     */
    public function getModule(string $className): AppAware
    {
        $property = $this->build->modulesClasses[$className];
        return $this->$property;
    }

    /**
     * This method is called internally on __construct & __unserialize
     * @param string $message
     * @return void
     */
    private function isReady(string $message): void
    {
        // PHP default timezone configuration,
        // Lifecycle entries require timestamps to function properly:
        date_default_timezone_set($this->config->timezone->getTimezoneId());

        // Cache Events
        $this->cache->events->onConnected()->listen(function (CacheDriverInterface $cacheDriver) {
            $this->events->onCacheConnection()->trigger([$cacheDriver]);
        });

        // Initialize Lifecycle
        $this->lifecycle = new Lifecycle();
        $this->lifecycle->log($message);

        // All set!
        $this->errors->exceptionHandlerShowTrace = false;
    }

    /**
     * Bootstraps dependant modules
     * @return void
     */
    public function bootstrap(): void
    {
        // Bootstrap dependants:
        $this->databases->bootstrap($this);

        // Lifecycle Entries:
        $this->lifecycle->bootstrappedOn = microtime(true);
        if (isset($this->lifecycle->startedOn)) {
            $this->lifecycle->loadTime = number_format($this->lifecycle->bootstrappedOn - $this->lifecycle->startedOn, 4);
            $this->lifecycle->log("App bootstrapped", $this->lifecycle->loadTime . "s", true);
        }
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $data = [
            "build" => $this->build,
            "cache" => $this->cache,
            "config" => $this->config,
            "databases" => $this->databases,
            "directories" => $this->directories,
            "errors" => $this->errors,
            "events" => $this->events,
            "lifecycle" => null
        ];

        foreach ($this->build->modulesProperties as $property) {
            $data[$property] = $this->$property;
        }

        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->build = $data["build"];
        $this->cache = $data["cache"];
        $this->config = $data["config"];
        $this->databases = $data["databases"];
        $this->directories = $data["directories"];
        $this->errors = $data["errors"];
        $this->events = $data["events"];
        foreach ($this->build->modulesProperties as $property) {
            $data[$property] = $this->$property;
        }

        $this->isReady("Restore app states successful");
    }
}