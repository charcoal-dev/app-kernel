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
 * @template-covariant T of object
 */
interface ConfigCollectorInterface
{
    public function count(): int;

    /**
     * @return array<string, T>
     */
    public function getCollection(): array;
}