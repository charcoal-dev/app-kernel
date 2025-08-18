<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Domain;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\Base\Traits\ControlledSerializableTrait;

/**
 * Class AbstractAppAware
 * @package Charcoal\App\Kernel\Domain
 */
abstract class AbstractAppAware
{
    use ControlledSerializableTrait;

    public readonly AbstractApp $app;

    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
    }
}