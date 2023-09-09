<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel;

use Charcoal\Events\Event;
use Charcoal\Events\EventsRegistry;

/**
 * Class Events
 * @package Charcoal\Apps\Kernel
 */
class Events extends EventsRegistry
{
    /**
     * @return \Charcoal\Events\Event
     */
    public function onDbConnection(): Event
    {
        return $this->on("db.connection");
    }

    /**
     * @return \Charcoal\Events\Event
     */
    public function onCacheConnection(): Event
    {
        return $this->on("cache.connection");
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        $this->events = [];
        return parent::__serialize();
    }
}
