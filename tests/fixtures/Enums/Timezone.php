<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Enums;

use Charcoal\App\Kernel\Contracts\Time\TimezoneInterface;

/**
 * Class Timezone
 * @package Charcoal\Tests\App\Fixtures\Enums
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