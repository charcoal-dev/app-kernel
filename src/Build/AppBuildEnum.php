<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

/**
 * Interface AppBuildEnum
 * @package Charcoal\App\Kernel\Build
 */
interface AppBuildEnum
{
    /**
     * Return name used as suffix to store build cache file
     * @return string
     */
    public function getName(): string;

    /**
     * Return BuildPlan for AppKernel
     * @return BuildPlan
     */
    public function getBuildPlan(): BuildPlan;
}