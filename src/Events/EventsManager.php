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
}