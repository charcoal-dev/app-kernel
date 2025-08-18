<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Domain;

use Charcoal\App\Kernel\AbstractApp;

/**
 * Interface AppBootstrappableInterface
 * @package Charcoal\App\Kernel\Contracts\Domain
 */
interface AppBootstrappableInterface
{
    public function bootstrap(AbstractApp $app): void;
}