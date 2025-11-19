<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Provides utility methods for validating and formatting contact information.
 * @api
 */
abstract class ContactHelper
{
    /**
     * @param mixed $input
     * @param string $delimiter
     * @return bool
     * @api
     */
    public static function isValidPhoneNumber(mixed $input, string $delimiter = "."): bool
    {
        if (is_string($input) && NumberHelper::inRange(strlen($input), 5, 20)) {
            if (preg_match('/\A\+\d{1,6}(' . preg_quote($delimiter) . '\d{1,14})+\z/', $input)) {
                return static::phoneToE164($input) !== false;
            }
        }

        return false;
    }

    /**
     * Get input value as E.164 format phone number
     * @param mixed $input
     * @return false|string
     */
    public static function phoneToE164(mixed $input): false|string
    {
        if (is_string($input)) {
            $input = "+" . preg_replace('/\D/', "", $input);
            if (preg_match('/\A\+[1-9][0-9]{4,14}\z/', $input)) {
                return $input;
            }
        }

        return false;
    }

    /**
     * @param mixed $input
     * @param bool $onlyAscii
     * @param int $maxLength
     * @return bool
     * @api
     */
    public static function isValidEmailAddress(mixed $input, bool $onlyAscii = true, int $maxLength = 254): bool
    {
        if (is_string($input) && NumberHelper::inRange(strlen($input), 6, $maxLength)) {
            if ($onlyAscii) {
                if (!preg_match('/\A[\w@\-._+]+\z/', $input)) {
                    return false;
                }
            }

            return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
        }

        return false;
    }

    /**
     * @param mixed $input
     * @param int $maxLength
     * @return bool
     * @api
     */
    public static function isValidUsername(mixed $input, int $maxLength = 20): bool
    {
        if (is_string($input) && NumberHelper::inRange(strlen($input), 4, $maxLength)) {
            return preg_match('/\A[a-zA-Z0-9]+[a-zA-Z0-9\-_]?[a-zA-Z0-9]+\z/', $input);
        }

        return false;
    }
}