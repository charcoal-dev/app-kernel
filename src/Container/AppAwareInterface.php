<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppBuild;

/**
 * Interface AppAwareInterface
 * @package Charcoal\App\Kernel\Container
 */
interface AppAwareInterface
{
    public function bootstrap(AppBuild $app): void;
}