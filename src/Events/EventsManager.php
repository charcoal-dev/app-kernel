<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Events;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Enums\DiagnosticsEvent;
use Charcoal\App\Kernel\Enums\ServerApiEvent;
use Charcoal\App\Kernel\Internal\Services\AppServiceConfigAwareInterface;

/**
 * Class EventsManager
 * @package Charcoal\App\Kernel\Events
 */
readonly class EventsManager implements AppServiceConfigAwareInterface
{
    /**
     * @param AbstractApp $app
     */
    public function __construct(protected AbstractApp $app)
    {
    }

    /**
     * @param DiagnosticsEvent $event
     * @param \Closure $closure
     * @return void
     */
    public function diagnostics(DiagnosticsEvent $event, \Closure $closure): void
    {
        $this->app->diagnostics->subscribe($event, $closure);
    }

    /**
     * @param DatabaseEnumInterface $db
     * @return DatabaseEvents
     */
    public function database(DatabaseEnumInterface $db): DatabaseEvents
    {
        return new DatabaseEvents($this->app, $db);
    }

    /**
     * @param CacheStoreEnumInterface $cacheStore
     * @return CacheEvents
     */
    public function cache(CacheStoreEnumInterface $cacheStore): CacheEvents
    {
        return new CacheEvents($this->app, $cacheStore);
    }

    /**
     * @param ServerApiEvent $event
     * @param \Closure $closure
     * @return void
     * @api
     */
    public function sapi(ServerApiEvent $event, \Closure $closure): void
    {
        $this->app->sapi->events->subscribe($event, $closure);
    }
}