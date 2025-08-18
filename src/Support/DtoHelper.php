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
     * @param \Throwable $t
     * @param bool $previous
     * @param bool $trace
     * @return array
     */
    public static function getExceptionObject(
        \Throwable $t,
        bool       $previous = true,
        bool       $trace = true,
    ): array
    {
        $dto = [
            "class" => get_class($t),
            "message" => $t->getMessage(),
            "code" => $t->getCode(),
            "file" => $t->getFile(),
            "line" => $t->getLine(),
        ];

        if ($previous) {
            $dto["previous"] = $t->getPrevious() ?
                static::getExceptionObject($t->getPrevious()) : null;
        }

        if ($trace) {
            $dto["trace"] = array_map(function (array $trace) {
                unset($trace["args"], $trace["object"]);
                return $trace;
            }, $t->getTrace());
        }

        return $dto;
    }

    /**
     * @param array|object $object
     * @param int $maxDepth
     * @param bool $normalizeCommonShapes
     * @param bool $checkRecursion
     * @param string|callable|null $onRecursion
     * @return array
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