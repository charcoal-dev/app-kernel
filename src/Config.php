<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\App\Kernel\Config\CacheConfig;
use Charcoal\App\Kernel\Config\DatabaseConfig;
use Charcoal\App\Kernel\DateTime\TimezoneInterface;

/**
 * Class Config
 * @package Charcoal\App\Kernel
 */
class Config
{
    public function __construct(
        public readonly TimezoneInterface $timezone,
        public readonly CacheConfig       $cache,
        public readonly DatabaseConfig    $database,
    )
    {
    }
}