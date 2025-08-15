<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

/**
 * Interface AppBuildEnum
 * @package Charcoal\App\Kernel\Build
 */
interface AppBuildEnum extends \UnitEnum
{
    /**
     * Return name used as suffix to store build cache file
     * @return string
     */
    public function getName(): string;

    /**
     * Return BuildPlan for AppKernel
     * @param AppBuildPartial $app
     * @return BuildPlan
     */
    public function getBuildPlan(AppBuildPartial $app): BuildPlan;

    /**
     * Determines if build will set and use its own error handlers
     * @return bool
     */
    public function setErrorHandlers(): bool;
}