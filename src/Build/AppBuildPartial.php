<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

use Charcoal\App\Kernel\Cache\CacheManager;
use Charcoal\App\Kernel\Config;
use Charcoal\App\Kernel\Database\DatabaseManager;
use Charcoal\App\Kernel\Directories;
use Charcoal\App\Kernel\Errors\ErrorHandler;
use Charcoal\App\Kernel\Events;

/**
 * Class AppBuildPartial is used mid-build provides early access to components while building modules/services
 * @package Charcoal\App\Kernel\Build
 */
class AppBuildPartial
{
    public function __construct(
        public readonly CacheManager    $cache,
        public readonly Config          $config,
        public readonly DatabaseManager $database,
        public readonly Directories     $directories,
        public readonly ErrorHandler    $errors,
        public readonly Events          $events,
    )
    {
    }
}