<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Events;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Internal\Services\AppServiceConfigAwareInterface;

/**
 * Class EventsManager
 * @package Charcoal\App\Kernel\Events
 */
readonly class EventsManager implements AppServiceConfigAwareInterface
{
    public DatabaseEvents $dataStore;

    /**
     * @param AbstractApp $app
     */
    public function __construct(AbstractApp $app)
    {
    }
}