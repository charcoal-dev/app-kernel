<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

/**
 * Class BuildMetadata
 * @package Charcoal\App\Kernel\Build
 */
readonly class BuildMetadata
{
    public function __construct(
        public AppBuildEnum $enum,
        public int          $timestamp,
        public array        $modulesClasses,
        public array        $modulesProperties,
    )
    {
    }
}