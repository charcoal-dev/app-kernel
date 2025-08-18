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
use Charcoal\App\Kernel\Events\EventsManager;
use Charcoal\App\Kernel\Internal\DomainBundle;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\App\Kernel\Internal\Services\ServicesBundle;
use Charcoal\App\Kernel\Time\Clock;
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
     * @param DirectoryNode $root
     * @return ServicesBundle
     * @internal
     */
    final public function appServices(AbstractApp $app, DirectoryNode $root): ServicesBundle
    {
        return new ServicesBundle(
            new Clock($app),
            new EventsManager($app),
            new CacheManager($app),
            new DatabaseManager($app),
            new PathRegistry($root->path)
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
}