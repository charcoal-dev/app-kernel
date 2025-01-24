<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\Events\Event;
use Charcoal\Events\EventsRegistry;

/**
 * Class Events
 * @package Charcoal\App\Kernel
 */
class Events extends EventsRegistry
{
    /**
     * Event when a DB connection is established,
     * Listener functions receive instance of \Charcoal\Database\Database as first argument
     * @return Event
     */
    public function onDbConnection(): Event
    {
        return $this->on("db.connection");
    }

    /**
     * Event when connection is established with cache server
     * @return Event
     */
    public function onCacheConnection(): Event
    {
        return $this->on("cache.connection");
    }

    /**
     * Purges all stored Event objects.
     * Listener callbacks are established after app is bootstrapped.
     * @return array
     */
    public function __serialize(): array
    {
        $this->events = [];
        return parent::__serialize();
    }
}