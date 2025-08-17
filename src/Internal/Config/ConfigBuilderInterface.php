<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Config;

/**
 * Interface ConfigBuilderInterface
 * @package Charcoal\App\Kernel\Internal\Config
 * @template T of ConfigSnapshotInterface
 */
interface ConfigBuilderInterface
{
    /**
     * @return T
     */
    public function build(): object;
}