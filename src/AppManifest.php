<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Cache\CacheManager;
use Charcoal\App\Kernel\Contracts\Domain\AppBindableInterface;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Errors\ErrorManager;
use Charcoal\App\Kernel\Events\EventsManager;
use Charcoal\App\Kernel\Internal\DomainBundle;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\App\Kernel\Internal\Services\ServicesBundle;
use Charcoal\App\Kernel\Clock\Clock;
use Charcoal\App\Kernel\Security\SecurityService;
use Charcoal\Filesystem\Node\DirectoryNode;

/**
 * Class AppManifest
 * @package Charcoal\App\Kernel
 */
class AppManifest
{
    /** @var array<array<\UnitEnum, callable(AbstractApp): AppBindableInterface>> */
    private array $domain = [];

    /**
     * @param AbstractApp $app
     * @return ServicesBundle
     * @internal
     */
    final public function appServices(AbstractApp $app): ServicesBundle
    {
        return new ServicesBundle(
            new Clock($app->config->timezone),
            $this->resolveEventsManager($app),
            $this->resolveDatabaseManager($app),
            $this->resolveCacheManager($app),
            new SecurityService($app)
        );
    }

    /**
     * @param \UnitEnum $key
     * @param callable(AbstractApp): AppBindableInterface $factory
     * @return $this
     * @api
     */
    final protected function bind(\UnitEnum $key, callable $factory): self
    {
        $this->domain[] = [$key, $factory];
        return $this;
    }

    /**
     * @param AbstractApp $app
     * @return DomainBundle
     */
    final public function getDomain(AbstractApp $app): DomainBundle
    {
        return new DomainBundle($app, $this->domain);
    }

    /**
     * Provides an instance of the ErrorManager service configured with the application's environment and root path.
     */
    public function resolveErrorService(AppEnv $env, PathRegistry $paths): ErrorManager
    {
        return new ErrorManager($env, $paths);
    }

    /**
     * Provides an instance of the PathRegistry service which may be required to resolve configuration files.
     */
    public function resolvePathsRegistry(AppEnv $env, DirectoryNode $root): PathRegistry
    {
        return new PathRegistry($env, $root->path);
    }

    /**
     * Instantiates and returns an EventsManager object configured with the provided application instance.
     */
    protected function resolveEventsManager(AbstractApp $app): EventsManager
    {
        return new EventsManager($app);
    }

    /**
     * Provides an instance of the DatabaseManager configured with the application's database settings.
     */
    protected function resolveDatabaseManager(AbstractApp $app): DatabaseManager
    {
        return new DatabaseManager($app->config->database);
    }

    /**
     * Resolves and provides an instance of the CacheManager configured with the application's cache settings.
     */
    protected function resolveCacheManager(AbstractApp $app): CacheManager
    {
        return new CacheManager($app->config->cache);
    }
}