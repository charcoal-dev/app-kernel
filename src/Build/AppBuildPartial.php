<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

use Charcoal\App\Kernel\CachePool;
use Charcoal\App\Kernel\Config;
use Charcoal\App\Kernel\Databases;
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
        public readonly CachePool    $cache,
        public readonly Config       $config,
        public readonly Databases    $databases,
        public readonly Directories  $directories,
        public readonly ErrorHandler $errors,
        public readonly Events       $events,
    )
    {
    }
}