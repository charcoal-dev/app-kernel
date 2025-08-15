<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Time;

use Charcoal\App\Kernel\Contracts\ClockInterface;
use Charcoal\App\Kernel\Contracts\TimezoneInterface;

/**
 * Class Clock
 * @package Charcoal\App\Kernel\Time
 */
final readonly class Clock implements ClockInterface
{
    private \DateTimeZone $timezone;

    /**
     * @throws \DateInvalidTimeZoneException
     */
    public function __construct(TimezoneInterface $timezone)
    {
        $this->timezone = new \DateTimeZone($timezone->getTimezoneId());
    }

    /**
     * @return \DateTimeImmutable
     */
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable("now", $this->timezone);
    }

    /**
     * @return int
     */
    public function timestamp(): int
    {
        return $this->now()->getTimestamp();
    }
}