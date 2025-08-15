<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Build\AppBuildStage;
use Charcoal\App\Kernel\Build\AppSerializable;
use Charcoal\App\Kernel\Build\BuildContext;
use Charcoal\App\Kernel\Cache\CacheManager;
use Charcoal\App\Kernel\Cipher\CipherKeychain;
use Charcoal\App\Kernel\Contracts\AppBuildEnum;
use Charcoal\App\Kernel\Contracts\Error\ErrorLoggerInterface;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Errors\ErrorHandler;
use Charcoal\App\Kernel\Stubs\NullErrorLog;
use Charcoal\App\Kernel\Time\Clock;
use Charcoal\Base\Traits\NotCloneableTrait;

/**
 * Class AbstractApp
 * @package Charcoal\App\Kernel
 */
abstract class AbstractApp extends AppSerializable
{
    public readonly BuildContext $build;
    public readonly CacheManager $cache;
    public readonly AppConfig $config;
    public readonly DatabaseManager $database;
    public readonly ErrorHandler $errors;
    public readonly Clock $clock;
    public readonly Lifecycle $lifecycle;

    use NotCloneableTrait;

    public function __construct(
        AppBuildEnum                   $context,
        ErrorLoggerInterface           $errorLog = new NullErrorLog(),
        public readonly Directories    $directories,
        public readonly CipherKeychain $cipher,
        public readonly Events         $events,
    )
    {
        $this->config = $this->declareErrorHandler($errorLog, $context)
            ->resolveConfig();

        $this->declareConfigAwareComponents();

        // Register child modules as declared by the downstream app,
        // and provides them with AppBuildStage to construct themselves:
        list($moduleClasses, $moduleProperties) = $this->registerModuleManifest($context,
            new AppBuildStage(
                $this->cache,
                $this->clock,
                $this->config,
                $this->database,
                $this->directories,
                $this->errors,
                $this->events
            )
        );

        $this->build = new BuildContext($context, $this->clock->now(), $moduleClasses, $moduleProperties);
        $this->isReady("New app instance instantiated");
    }

    /**
     * @param ErrorLoggerInterface $logger
     * @param AppBuildEnum $context
     * @return $this
     * @internal
     */
    protected function declareErrorHandler(ErrorLoggerInterface $logger, AppBuildEnum $context): static
    {
        new ErrorHandler($this, $context, $logger);
        return $this;
    }

    /**
     * @return void
     * @internal
     */
    protected function declareConfigAwareComponents(): void
    {
        $this->clock = new Clock($this->config->timezone);
        $this->database = new DatabaseManager();
        $this->cache = new CacheManager();
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
        $this->errors->exceptionHandlerShowTrace = false;
    }

    /**
     * Bootstraps dependant modules
     * @return void
     */
    public function bootstrap(): void
    {
        // Bootstrap dependants:
        $this->database->bootstrap($this);
        $this->cache->bootstrap($this);

        // All declared services and modules:
        foreach ($this->build->moduleProperties as $property) {
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
            "build" => $this->build,
            "cache" => $this->cache,
            "cipher" => $this->cipher,
            "config" => $this->config,
            "database" => $this->database,
            "directories" => $this->directories,
            "errors" => $this->errors,
            "events" => $this->events,
            "lifecycle" => null
        ];

        foreach ($this->build->moduleProperties as $property) {
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
        $this->cipher = $data["cipher"];
        $this->config = $data["config"];
        $this->database = $data["database"];
        $this->directories = $data["directories"];
        $this->errors = $data["errors"];
        $this->events = $data["events"];
        foreach ($this->build->moduleProperties as $property) {
            $this->$property = $data[$property];
        }

        if ($this->build->enum->deployErrorHandlers()) {
            $this->errors->setHandlers();
        }

        $this->isReady("Restore app states successful");
    }
}