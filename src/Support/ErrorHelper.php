<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * This class provides helper methods for handling and formatting error information.
 * It extends the base ErrorHelper class to include additional functionality.
 */
abstract readonly class ErrorHelper extends \Charcoal\Base\Support\Helpers\ErrorHelper
{
    /**
     * @param \Throwable $t
     * @param string $format
     * @return string
     */
    public static function exception2String(\Throwable $t, string $format = '[%1$s][#%2$s] %3$s'): string
    {
        return sprintf($format, get_class($t), $t->getCode(), $t->getMessage());
    }
}