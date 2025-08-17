<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\Time\TimezoneInterface;

/**
 * Class AppConfig
 * @package Charcoal\App\Kernel
 */
readonly class AppConfig
{
    public function __construct(
        public TimezoneInterface      $timezone,
        public ?CacheManagerConfig    $cache,
        public ?DatabaseManagerConfig $database,
    )
    {
    }
}