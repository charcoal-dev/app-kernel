<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Represents the application environment.
 * Provides utility methods to determine debug and error handling behavior
 * based on the environment.
 */
enum AppEnv: string
{
    case Dev = "dev";
    case Prod = "prod";
    case Test = "test";

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return match ($this) {
            self::Prod => false,
            default => true,
        };
    }

    /**
     * @return bool
     */
    public function deployErrorHandlers(): bool
    {
        return $this !== self::Test;
    }
}