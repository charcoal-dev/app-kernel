<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Time;

use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;

/**
 * Interface ClockInterface
 * @package Charcoal\App\Kernel\Contracts
 */
interface ClockEnumInterface extends TimezoneEnumInterface
{
    public function now(): \DateTimeImmutable;

    public function timestamp(): int;
}