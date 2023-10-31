<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Errors;

/**
 * Trait StaticErrorHelpers
 * @package Charcoal\Apps\Kernel\Errors
 */
trait StaticErrorHelpers
{
    /**
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
     * @param \Throwable $t
     * @return string
     */
    public static function Exception2String(\Throwable $t): string
    {
        return sprintf('[%s][#%s] %s', get_class($t), $t->getCode(), $t->getMessage());
    }
}
