<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppKernel;
use Charcoal\OOP\Traits\ControlledSerializableTrait;

/**
 * Class AppAware
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAware
{
    use ControlledSerializableTrait;

    public readonly AppKernel $app;

    /**
     * AppAware implementers receive instance of AppKernel via this method
     * @param AppKernel $app
     * @return void
     */
    public function bootstrap(AppKernel $app): void
    {
        $this->app = $app;
    }
}