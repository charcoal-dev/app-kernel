<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Clock;

use Charcoal\App\Kernel\AbstractApp;

/**
 * Trait StaticClockTrait
 * @package Charcoal\App\Kernel\Clock
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
     * @return \DateTimeImmutable
     */
    public static function now(): \DateTimeImmutable
    {
        return static::resolveInstance()->immutable("now");
    }

    /**
     * @param AbstractApp $app
     * @return static
     * @internal
     */
    public static function initializeStatic(AbstractApp $app): static
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