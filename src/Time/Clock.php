<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Time;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Time\ClockEnumInterface;
use Charcoal\App\Kernel\Internal\Services\AppServiceConfigAwareInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;

/**
 * Class Clock
 * @package Charcoal\App\Kernel\Time
 */
final class Clock implements AppServiceConfigAwareInterface, ClockEnumInterface
{
    use StaticClockTrait;

    private \DateTimeZone $timezone;

    /**
     * @param AbstractApp $app
     */
    public function __construct(AbstractApp $app)
    {
        try {
            date_default_timezone_set($app->config->timezone->getTimezoneId());
            $this->timezone = new \DateTimeZone($app->config->timezone->getTimezoneId());
        } catch (\Exception $e) {
            throw new \RuntimeException(ErrorHelper::exception2String($e), 0, $e);
        }
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

    /**
     * @return string
     */
    public function getTimezoneId(): string
    {
        return $this->timezone->getName();
    }
}