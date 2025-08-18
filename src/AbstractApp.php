<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Cache\CacheManager;
use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Errors\ErrorManager;
use Charcoal\App\Kernel\Events\EventsManager;
use Charcoal\App\Kernel\Internal\AppContext;
use Charcoal\App\Kernel\Internal\AppEnv;
use Charcoal\App\Kernel\Internal\AppSerializable;
use Charcoal\App\Kernel\Internal\DomainBundle;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\App\Kernel\Time\Clock;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Filesystem\Node\DirectoryNode;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Class AbstractApp
 * @package Charcoal\App\Kernel
 */
abstract class AbstractApp extends AppSerializable
{
    use NotCloneableTrait;

    public readonly AppContext $context;
    public readonly CacheManager $cache;
    public readonly Clock $clock;
    public readonly AppConfig $config;
    public readonly DatabaseManager $database;
    public readonly ErrorManager $errors;
    public readonly EventsManager $events;
    public readonly Lifecycle $lifecycle;
    public readonly PathRegistry $paths;
    // public readonly Security $security;

    public readonly DomainBundle $domain;

    private bool $bootstrapped = false;

    final public function __construct(AppEnv $env, DirectoryNode $root)
    {
        $this->errors = $this->declareErrorHandler($env, $root->path);
        if ($this->errors->policy->enabled) {
            $this->errors->setHandlers();
        }

        // Resolve AppConfig object
        $this->config = $this->resolveConfig();

        // Resolve AppManifest object, and declare services
        $manifest = $this->resolveAppManifest();
        foreach ($manifest->appServices($this, $root) as $service) {
            match (true) {
                $service instanceof Clock => $this->clock = $service,
                $service instanceof CacheManager => $this->cache = $service,
                $service instanceof DatabaseManager => $this->database = $service,
                $service instanceof EventsManager => $this->events = $service,
                $service instanceof PathRegistry => $this->paths = $service,
                default => throw new \RuntimeException("Unknown service: " . get_class($service)),
            };
        }

        // Instantiate all domain defined modules and services
        $this->domain = $manifest->getDomain($this);

        // Set context and invoke isReady > onReady hooks
        $this->context = new AppContext($env, $this->domain->inspect(), $this->clock->now());
        $this->isReady("New app instance instantiated");
    }

    /**
     * @param AppEnv $env
     * @param PathInfo $root
     * @return ErrorManager
     */
    protected function declareErrorHandler(AppEnv $env, PathInfo $root): ErrorManager
    {
        return new ErrorManager($env, $root);
    }

    /**
     * @return AppConfig
     */
    abstract protected function resolveConfig(): AppConfig;

    /**
     * @return AppManifest
     */
    abstract protected function resolveAppManifest(): AppManifest;

    /**
     * This method is called internally on __construct and __unserialize
     * @param string $message
     * @return void
     * @internal
     */
    private function isReady(string $message): void
    {
        // Todo: Initialize Lifecycle
        $this->lifecycle = new Lifecycle();
        $this->lifecycle->log($message);

        $this->onReadyCallback();
    }

    /**
     * @return void
     */
    protected function onReadyCallback(): void
    {
    }

    /**
     * Bootstraps dependant modules
     * @return void
     */
    public function bootstrap(): void
    {
        if ($this->bootstrapped) {
            throw new \BadMethodCallException("App is already bootstrapped");
        }

        $this->bootstrapped = true;

        // All declared services and modules:
        $this->domain->bootstrap($this);

        // Todo: Lifecycle Entries:
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
        return [
            "context" => $this->context,
            "cache" => $this->cache,
            "config" => $this->config,
            "clock" => $this->clock,
            //"cipher" => $this->cipher,
            "database" => $this->database,
            "errors" => $this->errors,
            "events" => $this->events,
            "lifecycle" => null,
            "paths" => $this->paths,
            "domain" => $this->domain,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->context = $data["context"];
        $this->errors = $data["errors"];
        if ($this->errors->policy->enabled) {
            $this->errors->setHandlers();
        }

        $this->config = $data["config"];
        $this->clock = $data["clock"];
        $this->cache = $data["cache"];
        //$this->cipher = $data["cipher"];
        $this->database = $data["database"];
        $this->events = $data["events"];
        $this->paths = $data["paths"];
        $this->domain = $data["domain"];
        $this->bootstrapped = false;

        $this->isReady("Restore app states successful");
    }
}