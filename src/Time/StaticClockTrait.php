<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Time;

use Charcoal\App\Kernel\Build\AppBuildStage;

/**
 * Trait StaticClockTrait
 * @package Charcoal\App\Kernel\Time
 * @internal
 */
trait StaticClockTrait
{
    private static ?self $instance = null;

    /**
     * @return int
     */
    public static function getTimestamp(): int
    {
        return static::resolveInstance()->getTimestamp();
    }

    /**
     * @param AppBuildStage $app
     * @return static
     */
    public static function staticScopeInit(AppBuildStage $app): static
    {
        return static::$instance = $app->clock;
    }

    /**
     * @return static
     */
    protected static function resolveInstance(): static
    {
        if (!isset(static::$instance)) {
            throw new \RuntimeException("Clock service was not instantiated in global scope");
        }

        return static::$instance;
    }
}