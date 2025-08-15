<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Time;

use Charcoal\App\Kernel\Contracts\TimezoneInterface;

/**
 * Class Timezone
 * @package Charcoal\App\Kernel\DateTime
 */
enum Timezone: string implements TimezoneInterface
{
    case UTC = "UTC";
    case EUROPE_LONDON = "Europe/London";
    case ASIA_DUBAI = "Asia/Dubai";
    case ASIA_ISLAMABAD = "Asia/Karachi";

    /**
     * @return string
     */
    public function getTimezoneId(): string
    {
        return $this->value;
    }
}