<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Internal\Config\ErrorManagerConfig;

/**
 * Class AppEnv
 * @package Charcoal\App\Kernel\Internal
 * @internal
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
     * @return ErrorManagerConfig
     */
    public function errorManagerPolicy(): ErrorManagerConfig
    {
        return new ErrorManagerConfig(
            $this !== self::Test,
            $this !== self::Test ? "./log/error.log" : null
        );
    }
}