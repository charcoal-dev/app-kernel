<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Clock;

use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;

/**
 * Interface ClockEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Clock
 */
interface ClockInterface extends TimezoneEnumInterface
{
    public static function now(): \DateTimeImmutable;

    public static function getTimestamp(): int;

    public function immutable(string $datetime = "now"): \DateTimeImmutable;

    public function timestamp(): int;
}