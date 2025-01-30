<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\OOP\Traits\ControlledSerializableTrait;

/**
 * Class AppAware
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAware
{
    use ControlledSerializableTrait;

    public readonly AppBuild $app;

    /**
     * AppAware implementers receive instance of AppBuild via this method
     * @param AppBuild $app
     * @return void
     */
    public function bootstrap(AppBuild $app): void
    {
        $this->app = $app;
    }
}