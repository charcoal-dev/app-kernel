<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

/**
 * Interface TimezoneInterface
 * @package Charcoal\App\Kernel\DateTime
 */
interface TimezoneEnumInterface
{
    /**
     * Return timezone ID that is acceptable with PHP \DateTime
     * @return string
     */
    public function getTimezoneId(): string;
}
