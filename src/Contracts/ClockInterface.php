<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

/**
 * Interface ClockInterface
 * @package Charcoal\App\Kernel\Contracts
 */
interface ClockInterface
{
    public function __construct(TimezoneInterface $timezone);

    public function now(): \DateTimeImmutable;

    public function timestamp(): int;
}