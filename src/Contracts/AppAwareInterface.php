<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

use Charcoal\App\Kernel\AppBuild;

/**
 * Interface AppAwareInterface
 * @package Charcoal\App\Kernel\Contracts
 */
interface AppAwareInterface
{
    /**
     * @param AppBuild $app
     * @return void
     */
    public function bootstrap(AppBuild $app): void;
}