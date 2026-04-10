<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Clock;

use Charcoal\App\Kernel\Contracts\Clock\ClockInterface;
use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\Base\Objects\Traits\InstanceOnStaticScopeTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;

/**
 * Provides functionalities to manage time and datetime operations with timezone awareness.
 * Implements methods for retrieving timestamps, creating immutable datetime objects,
 * and accessing the system's configured timezone.
 */
final class Clock implements AppServiceInterface, ClockInterface
{
    use InstanceOnStaticScopeTrait;
    use NotCloneableTrait;

    private \DateTimeZone $timezone;

    /**
     * @param TimezoneEnumInterface $timezone
     */
    public function __construct(TimezoneEnumInterface $timezone)
    {
        try {
            date_default_timezone_set($timezone->getTimezoneId());
            $this->timezone = new \DateTimeZone($timezone->getTimezoneId());
        } catch (\Exception $e) {
            throw new \RuntimeException(ErrorHelper::exception2String($e), 0, $e);
        }
    }

    /**
     * @return int
     */
    public static function getTimestamp(): int
    {
        return self::now()->getTimestamp();
    }

    /**
     * @return \DateTimeImmutable
     */
    public static function now(): \DateTimeImmutable
    {
        return self::getInstance()->getImmutable("now");
    }

    /**
     * @param string $datetime
     * @return \DateTimeImmutable
     */
    public function getImmutable(string $datetime = "now"): \DateTimeImmutable
    {
        return new \DateTimeImmutable($datetime, $this->timezone);
    }

    /**
     * @return int
     */
    public function timestamp(): int
    {
        return $this->getImmutable("now")->getTimestamp();
    }

    /**
     * @return string
     */
    public function getTimezoneId(): string
    {
        return $this->timezone->getName();
    }
}