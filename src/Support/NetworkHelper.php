<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Helper class providing utility methods for network-related validation
 */
abstract readonly class NetworkHelper extends \Charcoal\Base\Support\Helpers\NetworkHelper
{
    /**
     * @param mixed $port
     * @return bool
     */
    public static function isValidPort(mixed $port): bool
    {
        return is_int($port) && $port >= 0 && $port <= 65535;
    }
}