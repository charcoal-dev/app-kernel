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
    /** @var array<string,AppBindableInterface> */
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
     * @param AppBindableInterface $module
     * @return $this
     * @api
     */
    public function bind(\UnitEnum $key, AppBindableInterface $module): self
    {
        $this->domain[$key->name] = $module;
        return $this;
    }

    /**
     * @return array<string,AppBindableInterface>
     * @internal
     */
    public function getDomain(): array
    {
        return $this->domain;
    }
}