<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\Base\Traits\ControlledSerializableTrait;

/**
 * Class AppAware
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAware
{
    use ControlledSerializableTrait;

    public readonly AbstractApp $app;

    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
    }
}