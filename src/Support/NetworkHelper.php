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
class NetworkHelper
{
    /**
     * Validates given argument as an IP address
     * Optionally allows IPv4 and/or IPv6 addresses
     * @param mixed $ip
     * @param bool $ipv4
     * @param bool $ipv6
     * @return bool
     */
    public static function isValidIpAddress(mixed $ip, bool $ipv4 = true, bool $ipv6 = false): bool
    {
        if (!is_string($ip) || trim($ip) === "") {
            return false;
        }

        $flags = match (true) {
            $ipv4 && $ipv6 => FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6,
            $ipv4 => FILTER_FLAG_IPV4,
            $ipv6 => FILTER_FLAG_IPV6,
            default => 0,
        };

        if (!$flags) {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }

    /**
     * Validates given argument as a domain or subdomain name
     * Optionally allows single words (like "localhost" or a docker service container)
     * Optionally allows IPv4 and/or IPv6 addresses
     * @param mixed $hostname
     * @param bool $allowSingleWord
     * @param bool $allowIpv4
     * @param bool $allowIpv6
     * @return bool
     */
    public static function isValidHostname(
        mixed $hostname,
        bool  $allowSingleWord = true,
        bool  $allowIpv4 = true,
        bool  $allowIpv6 = true
    ): bool
    {
        if (!is_string($hostname) || trim($hostname) === "") {
            return false;
        }

        if (preg_match('/^(?=.{1,253}$)(([a-z\d]([-a-z\d]*[a-z\d])?)\.)+[a-z]{2,63}$/i', $hostname)) {
            return true;
        }

        if ($allowSingleWord && preg_match('/^[a-z\d\-]{1,63}$/i', $hostname)) {
            return true;
        }

        if ($allowIpv4 || $allowIpv6) {
            return static::isValidIpAddress($hostname, $allowIpv4, $allowIpv6);
        }

        return false;
    }

    /**
     * @param mixed $port
     * @return bool
     */
    public static function isValidPort(mixed $port): bool
    {
        return is_int($port) && $port >= 0 && $port <= 65535;
    }
}