<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Enumeration representing digest algorithms.
 */
enum DigestAlgo: string
{
    case MD5 = "md5";
    case SHA1 = "sha1";
    case SHA256 = "sha256";
    case SHA512 = "sha512";
    case SHA3_224 = "sha3-224";
    case SHA3_256 = "sha3-256";
    case SHA3_512 = "sha3-512";
    case RIPEMD128 = "ripemd128";
    case RIPEMD160 = "ripemd160";
    case RIPEMD256 = "ripemd256";
    case RIPEMD320 = "ripemd320";
    case BLAKE2S_256 = "blake2s";
    case BLAKE2B_512 = "blake2b";
}