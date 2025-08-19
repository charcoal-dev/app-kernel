<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Cache\CacheManager;
use Charcoal\App\Kernel\Clock\Clock;
use Charcoal\App\Kernel\Clock\MonotonicTimestamp;
use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Diagnostics\Diagnostics;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Errors\ErrorManager;
use Charcoal\App\Kernel\Events\EventsManager;
use Charcoal\App\Kernel\Internal\AppContext;
use Charcoal\App\Kernel\Internal\AppSerializable;
use Charcoal\App\Kernel\Internal\DomainBundle;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\Base\Traits\ControlledSerializableTrait;
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
    use ControlledSerializableTrait;

    public readonly AppContext $context;
    public readonly CacheManager $cache;
    public readonly Clock $clock;
    public readonly AppConfig $config;
    public readonly DatabaseManager $database;
    public readonly ErrorManager $errors;
    public readonly EventsManager $events;
    public readonly PathRegistry $paths;
    // public readonly Security $security;
    public readonly DomainBundle $domain;
    public readonly Diagnostics $diagnostics;

    private bool $bootstrapped = false;

    final public function __construct(AppEnv $env, DirectoryNode $root)
    {
        $this->initializeDiagnostics();
        $manifest = $this->resolveAppManifest();

        // Error service and configurator may require paths to be defined first
        $this->paths = $manifest->resolvePathsRegistry($root);
        $this->errors = $manifest->resolveErrorService($env, $root);
        $this->initializeErrorService();
        $this->paths->declarePaths();

        // Resolve AppConfig object
        $this->config = $this->resolveAppConfig();

        // Resolve AppManifest object, and declare services
        foreach ($manifest->appServices($this) as $service) {
            match (true) {
                $service instanceof Clock => $this->clock = $service,
                $service instanceof CacheManager => $this->cache = $service,
                $service instanceof DatabaseManager => $this->database = $service,
                $service instanceof EventsManager => $this->events = $service,
                default => throw new \RuntimeException("Unknown service: " . get_class($service)),
            };
        }

        // Instantiate all domain defined modules and services
        Clock::initializeStatic($this->clock);
        $this->domain = $manifest->getDomain($this);

        // Set context and invoke isReady > onReady hooks
        $this->context = new AppContext($env, $this->clock->getImmutable(), $this->domain->inspect());
        $this->isReady("New app instance initialized");
    }

    /**
     * @return $this
     */
    private function initializeDiagnostics(): static
    {
        $this->diagnostics = Diagnostics::initialize();
        return $this;
    }

    /**
     * @return $this
     */
    private function initializeErrorService(): static
    {
        $this->errors->subscribe($this->diagnostics);
        if ($this->errors->policy) {
            $this->errors->setHandlers();
        }

        ErrorManager::initializeStatic($this->errors);
        return $this;
    }

    /**
     * @return AppConfig
     */
    abstract protected function resolveAppConfig(): AppConfig;

    /**
     * @return AppManifest
     */
    abstract protected function resolveAppManifest(): AppManifest;

    /**
     * This method is called internally on __construct and __unserialize
     * @internal
     */
    private function isReady(string $message): void
    {
        $this->diagnostics->verbose($message);
        $this->onReadyCallback();
    }

    /**
     * @return void
     */
    protected function onReadyCallback(): void
    {
    }

    /**
     * @param MonotonicTimestamp $startTime
     * @return void
     */
    public function bootstrap(MonotonicTimestamp $startTime): void
    {
        if ($this->bootstrapped) {
            throw new \BadMethodCallException("App is already bootstrapped");
        }

        $this->bootstrapped = true;
        $this->diagnostics->setStartupTime($startTime);

        // All declared services and modules:
        $this->domain->bootstrap($this);
    }

    /**
     * @return array
     */
    protected function collectSerializableData(): array
    {
        return [
            "context" => $this->context,
            "cache" => $this->cache,
            "config" => $this->config,
            "clock" => $this->clock,
            "database" => $this->database,
            "errors" => $this->errors,
            "events" => $this->events,
            "paths" => $this->paths,
            "domain" => $this->domain,
            "diagnostics" => null,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->bootstrapped = false;
        $this->context = $data["context"];
        $this->paths = $data["paths"];
        $this->errors = $data["errors"];
        $this->initializeDiagnostics()
            ->initializeErrorService();

        $this->config = $data["config"];
        $this->clock = $data["clock"];
        $this->cache = $data["cache"];
        $this->database = $data["database"];
        $this->events = $data["events"];

        Clock::initializeStatic($this->clock);
        $this->domain = $data["domain"];

        $this->isReady("Restore app states successful");
    }

    /**
     * @return \class-string[]
     */
    public static function unserializeDependencies(): array
    {
        $classmap = [static::class, AppEnv::class, AppContext::class, PathInfo::class, \DateTimeImmutable::class];
        $classmap = [...$classmap, ...CacheManager::unserializeDependencies()];
        $classmap[] = Clock::class;

        return $classmap;
    }
}