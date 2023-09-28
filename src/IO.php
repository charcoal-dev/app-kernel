<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel;

/**
 * Class IO
 * @package Charcoal\Apps\Kernel
 */
class IO
{
    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     */
    public function __construct(protected readonly AbstractApp $app)
    {
    }

    /**
     * @param mixed $var
     * @return string
     */
    public function getType(mixed $var): string
    {
        return is_object($var) ? get_class($var) : gettype($var);
    }

    /**
     * @param mixed $input
     * @return bool
     */
    public function toBool(mixed $input): bool
    {
        if (is_bool($input)) {
            return $input;
        }

        if ($input === 1) {
            return true;
        }

        if (is_string($input) && in_array(strtolower($input), ["1", "true", "on", "yes"])) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $ip
     * @param bool $ipv4
     * @param bool $ipv6
     * @return bool
     */
    public function isValidIPAddress(mixed $ip, bool $ipv4 = true, bool $ipv6 = false): bool
    {
        if (!is_string($ip) || !$ip) {
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

        return (bool)filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * @param object|array $object
     * @return array
     * @throws \JsonException
     */
    public function jsonFilter(object|array $object): array
    {
        return json_decode(json_encode($object, JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
    }
}
