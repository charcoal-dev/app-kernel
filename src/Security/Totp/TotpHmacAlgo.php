<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security\Totp;

/**
 * Enum representing the available HMAC algorithms for TOTP.
 */
enum TotpHmacAlgo
{
    case SHA1;

    public function createHmac(string $data, string $key): string
    {
        return match ($this) {
            self::SHA1 => hash_hmac("sha1", $data, $key, true),
        };
    }
}