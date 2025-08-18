<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Class DtoHelper
 * @package Charcoal\App\Kernel\Support
 */
class DtoHelper extends \Charcoal\Base\Support\Helpers\DtoHelper
{
    /**
     * Converts and sanitizes \Throwable object to a DTO
     */
    public static function getExceptionObject(
        \Throwable $t,
        bool       $previous = true,
        bool       $trace = true,
        int        $pathOffset = 0
    ): array
    {
        $dto = [
            "class" => get_class($t),
            "message" => $t->getMessage(),
            "code" => $t->getCode(),
            "file" => $t->getFile(),
            "line" => $t->getLine(),
        ];

        $offset = min(max(0, $pathOffset), strlen($dto["file"]));
        if ($offset > 0) {
            $dto["file"] = substr($dto["file"], $offset);
        }

        if ($previous) {
            $dto["previous"] = $t->getPrevious() ?
                static::getExceptionObject($t->getPrevious(), $previous, $trace, $pathOffset) : null;
        }

        if ($trace) {
            $dto["trace"] = array_map(function (array $trace) use ($pathOffset) {
                if ($pathOffset > 0 && isset($trace["file"])) {
                    $offset = min(max(0, $pathOffset), strlen($trace["file"]));
                    if ($offset > 0) {
                        $trace["file"] = substr($trace["trace"], $offset);
                    }
                }

                unset($trace["args"], $trace["object"]);
                return $trace;
            }, $t->getTrace());
        }

        return $dto;
    }


    /**
     * Converts an input array or object into a type-safe array representation.
     */
    public static function typeSafeArray(
        array|object         $object,
        int                  $maxDepth = 10,
        bool                 $normalizeCommonShapes = true,
        bool                 $checkRecursion = true,
        null|string|callable $onRecursion = null
    ): array
    {
        return self::createFrom($object, $maxDepth, $normalizeCommonShapes, $checkRecursion, $onRecursion);
    }
}