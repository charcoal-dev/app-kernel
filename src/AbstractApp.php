<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Context\AppSerializable;
use Charcoal\App\Kernel\Context\AppContext;
use Charcoal\App\Kernel\Cache\CacheManager;
use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Errors\ErrorManager;
use Charcoal\App\Kernel\Events\EventsManager;
use Charcoal\App\Kernel\Internal\AppEnv;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\App\Kernel\Internal\Services\ServicesBundle;
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
    public readonly AppContext $context;
    public readonly CacheManager $cache;
    // public readonly CipherKeychain $cipher;
    public readonly Clock $clock;
    public readonly AppConfig $config;
    public readonly DatabaseManager $database;
    public readonly ErrorManager $errors;
    public readonly EventsManager $events;
    public readonly Lifecycle $lifecycle;
    public readonly PathRegistry $paths;
    // public readonly Security $security;

    use NotCloneableTrait;

    public function __construct(public readonly AppEnv $env, DirectoryNode $root)
    {
        $this->config = $this->declareErrorHandler($root->path)
            ->resolveConfig();

        // Register child modules as declared by the downstream app,
        // and provides them with AbstractPath to construct themselves:
        foreach ($this->declareAppServices($root) as $service) {
            match (true) {
                $service instanceof Clock => $this->clock = $service,
                $service instanceof CacheManager => $this->cache = $service,
                $service instanceof DatabaseManager => $this->database = $service,
                $service instanceof EventsManager => $this->events = $service,
                $service instanceof PathRegistry => $this->paths = $service,
                default => throw new \RuntimeException("Unknown service: " . get_class($service)),
            };
        }

        //list($moduleClasses, $moduleProperties) = $this->registerModuleManifest($context);

        $this->context = new AppContext($this->clock->now(), $moduleClasses, $moduleProperties);
        $this->isReady("New app instance instantiated");
    }

    /**
     * @param PathInfo $root
     * @return $this
     */
    protected function declareErrorHandler(PathInfo $root): static
    {
        new ErrorManager($this->env, $root);
        return $this;
    }

    /**
     * @param DirectoryNode $root
     * @return ServicesBundle
     * @api
     */
    protected function declareAppServices(DirectoryNode $root): ServicesBundle
    {
        return new ServicesBundle(
            new Clock($this),
            new EventsManager($this),
            new CacheManager($this),
            new DatabaseManager($this),
            new PathRegistry($root->path)
        );
    }

    /**
     * @return AppConfig
     */
    abstract protected function resolveConfig(): AppConfig;

    /**
     * This method is called internally on __construct and __unserialize
     * @param string $message
     * @return void
     * @internal
     */
    private function isReady(string $message): void
    {
        // Initialize Lifecycle
        $this->lifecycle = new Lifecycle();
        $this->lifecycle->log($message);

        // All set!
    }

    /**
     * Bootstraps dependant modules
     * @return void
     */
    public function bootstrap(): void
    {
        // All declared services and modules:
        foreach ($this->context->moduleProperties as $property) {
            $this->$property->bootstrap($this);
        }

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
            "env" => $this->env,
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
        ];

        foreach ($this->context->moduleProperties as $property) {
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
        $this->env = $data["env"];
        $this->context = $data["context"];
        $this->config = $data["config"];
        $this->clock = $data["clock"];
        $this->cache = $data["cache"];
        //$this->cipher = $data["cipher"];
        $this->database = $data["database"];
        //$this->directories = $data["directories"];
        $this->errors = $data["errors"];
        $this->events = $data["events"];
        $this->paths = $data["paths"];
        foreach ($this->context->moduleProperties as $property) {
            $this->$property = $data[$property];
        }

        if ($this->errors->policy->enabled) {
            $this->errors->setHandlers();
        }

        $this->isReady("Restore app states successful");
    }
}