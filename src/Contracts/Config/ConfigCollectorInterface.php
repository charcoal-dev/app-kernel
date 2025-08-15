<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Config;

/**
 * Interface ConfigCollectorInterface
 * @package Charcoal\App\Kernel\Contracts\Config
 */
interface ConfigCollectorInterface
{
    public function count(): int;

    public function getCollection(): array;
}