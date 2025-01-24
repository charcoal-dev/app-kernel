<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

/**
 * Class Errors
 * @package Charcoal\App\Kernel
 */
class Errors
{
    /**
     * Static helper to convert \Throwable object into an archive-able Array comprised purely of scalar data
     * @param \Throwable $t
     * @return array
     */
    public static function Exception2Array(\Throwable $t): array
    {
        $exception = [
            "class" => get_class($t),
            "message" => $t->getMessage(),
            "code" => $t->getCode(),
            "file" => $t->getFile(),
            "line" => $t->getLine(),
            "previous" => $t->getPrevious() ? static::Exception2Array($t->getPrevious()) : null,
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
     * Static helper to convert \Throwable object into a readable string
     * @param \Throwable $t
     * @return string
     */
    public static function Exception2String(\Throwable $t): string
    {
        return sprintf("[%s][#%s] %s", get_class($t), $t->getCode(), $t->getMessage());
    }
}