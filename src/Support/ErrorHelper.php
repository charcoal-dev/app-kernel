<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Class ErrorHelper
 * @package Charcoal\App\Kernel\Support
 */
class ErrorHelper extends \Charcoal\Base\Support\ErrorHelper
{
    /**
     * @param \Throwable $t
     * @return array
     */
    public static function exception2Array(\Throwable $t): array
    {
        $exception = [
            "class" => get_class($t),
            "message" => $t->getMessage(),
            "code" => $t->getCode(),
            "file" => $t->getFile(),
            "line" => $t->getLine(),
            "previous" => $t->getPrevious() ? static::exception2Array($t->getPrevious()) : null,
        ];

        $exception["trace"] = array_map(function (array $trace) {
            unset($trace["args"]);
            return $trace;
        }, $t->getTrace());

        // Charcoal libs-spec error
        if (property_exists($t, "error") && $t->error instanceof \BackedEnum) {
            $exception["errorCode"] = $t->error->name;
        }

        return $exception;
    }

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