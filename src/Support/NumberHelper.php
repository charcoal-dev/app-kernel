<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Class NumberHelper
 * @package Charcoal\App\Kernel\Support
 */
abstract readonly class NumberHelper
{
    /**
     * @api Checks if a number is within a range.
     */
    public static function inRange(mixed $input, int $min, int $max): bool
    {
        if (!is_int($input)) {
            return false;
        }

        return $input >= $min && $input <= $max;
    }

    /**
     * @api Cleans a string of trailing zeros and a decimal point.
     */
    public static function cleanFloatString(string $value): string
    {
        return strpos($value, ".") > 0 ? rtrim(rtrim($value, "0"), ".") : $value;
    }
}