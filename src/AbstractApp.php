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
use Charcoal\App\Kernel\Contracts\ServerApi\ServerApiEnumInterface;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Diagnostics\Diagnostics;
use Charcoal\App\Kernel\Diagnostics\Events\BuildStageEvents;
use Charcoal\App\Kernel\Domain\DomainBundle;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Enums\DiagnosticsEvent;
use Charcoal\App\Kernel\Errors\ErrorManager;
use Charcoal\App\Kernel\Events\EventsManager;
use Charcoal\App\Kernel\Internal\AppContext;
use Charcoal\App\Kernel\Internal\AppSerializable;
use Charcoal\App\Kernel\Internal\Enums\ConcreteEnumsResolver;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\App\Kernel\Security\SecurityService;
use Charcoal\App\Kernel\ServerApi\SapiBundle;
use Charcoal\Base\Objects\ObjectHelper;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Contracts\Sapi\ServerApiInterface;
use Charcoal\Events\Exceptions\SubscriptionClosedException;
use Charcoal\Filesystem\Node\DirectoryNode;
use Charcoal\Filesystem\Path\PathInfo;
use Charcoal\Http\Server\Middleware\MiddlewareRegistry;

/**
 * This class serves as the foundational base for applications, managing various services
 * such as cache management, routing, security, diagnostics, and error handling. It ensures
 * proper initialization and configuration of essential parts, facilitating the execution
 * of application-specific logic through extensions.
 */
abstract readonly class AbstractApp extends AppSerializable
{
    use NotCloneableTrait;
    use ControlledSerializableTrait;

    public AppContext $context;
    public CacheManager $cache;
    public Clock $clock;
    public AppConfig $config;
    public DatabaseManager $database;
    public ConcreteEnumsResolver $enums;
    public ErrorManager $errors;
    public EventsManager $events;
    public PathRegistry $paths;
    public SecurityService $security;
    public DomainBundle $domain;
    public Diagnostics $diagnostics;
    public SapiBundle $sapi;

    private bool $bootstrapped;

    final public function __construct(AppEnv $env, DirectoryNode $root, ?callable $monitor = null)
    {
        $this->initializeDiagnostics($monitor);
        $manifest = $this->resolveAppManifest();

        // Error service and configurator may require paths to be defined first
        $this->paths = $manifest->resolvePathsRegistry($env, $root);
        $this->enums = $manifest->resolveConcreteEnums();
        $this->errors = new ErrorManager($env, $this->paths);
        $this->initializeErrorService();
        $this->paths->acceptPathsDeclaration($this);
        if ($this->errors->policy) {
            $this->errorServiceDeployedHook();
        }

        // Resolve AppConfig object
        $this->config = $this->resolveAppConfig($env, $this->paths);

        // Resolve AppManifest object and declare services
        foreach ($manifest->appServices($this) as $service) {
            match (true) {
                $service instanceof Clock => $this->clock = $service,
                $service instanceof CacheManager => $this->cache = $service,
                $service instanceof DatabaseManager => $this->database = $service,
                $service instanceof EventsManager => $this->events = $service,
                $service instanceof SecurityService => $this->security = $service,
                default => throw new \RuntimeException("Unknown service: " . get_class($service)),
            };
        }

        // Instantiate all domain-defined modules and services
        Clock::initializeStatic($this->clock);
        $this->beforeDomainBundlesHook(false);
        $this->domain = $manifest->getDomain($this);

        // Check Server API Bundle...
        $this->sapi = $manifest->getSapiBundle($this);

        // Set context and invoke isReady > onReady hooks
        $this->context = new AppContext($env, $this->clock->getImmutable(), $this->domain->inspect());
        $this->isReady("New app instance initialized");
    }

    /**
     * @param bool $restore
     * @return void
     */
    protected function beforeDomainBundlesHook(bool $restore): void
    {
    }

    /**
     * @param callable|null $monitor
     * @return $this
     */
    private function initializeDiagnostics(?callable $monitor = null): static
    {
        $this->diagnostics = Diagnostics::initialize();
        if ($monitor) {
            try {
                $this->diagnostics->subscribe(DiagnosticsEvent::BuildStage,
                    function (BuildStageEvents $event) use ($monitor) {
                        $monitor($event);
                    });
            } catch (SubscriptionClosedException $e) {
                throw new \RuntimeException($e->getMessage(), previous: $e);
            }
        }

        $this->diagnostics->buildStageStream(BuildStageEvents::DiagnosticsReady);
        return $this;
    }

    /**
     * @return $this
     */
    private function initializeErrorService(): static
    {
        $this->diagnostics->buildStageStream(BuildStageEvents::ErrorServiceStarted);
        $this->errors->subscribe($this->diagnostics);
        if ($this->errors->policy) {
            $this->errors->setHandlers();
            $this->diagnostics->buildStageStream(BuildStageEvents::ErrorHandlersOn);
        }

        ErrorManager::initializeStatic($this->errors);
        return $this;
    }

    /**
     * @return void
     */
    protected function errorServiceDeployedHook(): void
    {
    }

    /**
     * @param AppEnv $env
     * @param PathRegistry $paths
     * @return AppConfig
     */
    abstract protected function resolveAppConfig(AppEnv $env, PathRegistry $paths): AppConfig;

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
        $this->diagnostics->buildStageStream(BuildStageEvents::Ready);
        $this->diagnostics->verbose($message);
        $this->onReadyCallback();
    }

    /**
     * @return void
     */
    protected function onReadyCallback(): void
    {
    }

    /** @internal */
    final public function setupHttpPipelinesHook(ServerApiEnumInterface $sapi, MiddlewareRegistry $mw): void
    {
        $this->declareHttpPipelines($sapi, $mw);
    }

    /**
     * @param ServerApiEnumInterface $sapi
     * @param MiddlewareRegistry $mw
     * @return void
     */
    protected function declareHttpPipelines(ServerApiEnumInterface $sapi, MiddlewareRegistry $mw): void
    {
    }

    /** @api */
    public function bootstrap(MonotonicTimestamp $startTime, ?ServerApiEnumInterface $sapi = null): ?ServerApiInterface
    {
        if (isset($this->bootstrapped) && $this->bootstrapped) {
            throw new \BadMethodCallException("App is already bootstrapped");
        }

        $sapi = $this->sapi->load($this, $sapi);

        // Bootstrap completed
        $this->bootstrapped = true;
        $this->diagnostics->buildStageStream(BuildStageEvents::Bootstrapped);
        $this->diagnostics->setStartupTime($this->clock, $startTime);

        // All declared services and modules:
        $this->security->bootstrap($this);
        $this->database->bootstrap($this);
        $this->domain->bootstrap($this);
        $this->diagnostics->verbose(ObjectHelper::baseClassName(static::class) . " bootstrapped");

        return $sapi;
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
            "enums" => $this->enums,
            "errors" => $this->errors,
            "events" => $this->events,
            "paths" => $this->paths,
            "domain" => $this->domain,
            "security" => $this->security,
            "sapi" => $this->sapi,
            "diagnostics" => null,
            "bootstrapped" => null,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->context = $data["context"];
        $this->paths = $data["paths"];
        $this->enums = $data["enums"];
        $this->errors = $data["errors"];
        $this->initializeDiagnostics(null)
            ->initializeErrorService();

        $this->config = $data["config"];
        $this->clock = $data["clock"];
        $this->cache = $data["cache"];
        $this->database = $data["database"];
        $this->events = $data["events"];

        Clock::initializeStatic($this->clock);
        $this->security = $data["security"];
        $this->beforeDomainBundlesHook(true);
        $this->domain = $data["domain"];
        $this->sapi = $data["sapi"];

        // Rehydrate ORM tables
        $this->database->tables->bootstrap($this->domain);

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