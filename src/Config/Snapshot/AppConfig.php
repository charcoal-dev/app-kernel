<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;

/**
 * Class AppConfig
 * @package Charcoal\App\Kernel
 */
readonly class AppConfig
{
    public function __construct(
        public TimezoneEnumInterface  $timezone,
        public ?CacheManagerConfig    $cache,
        public ?DatabaseManagerConfig $database,
    )
    {
    }
}