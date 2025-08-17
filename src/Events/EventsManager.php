<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Events;

readonly class EventsManager
{
    public DatabaseEvents $dataStore;

    public function __construct()
    {

    }
}