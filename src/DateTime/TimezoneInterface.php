<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\DateTime;

/**
 * Interface TimezoneInterface
 * @package Charcoal\App\Kernel\DateTime
 */
interface TimezoneInterface
{
    /**
     * Return timezone ID that is acceptable with PHP \DateTime
     * @return string
     */
    public function getTimezoneId(): string;
}
