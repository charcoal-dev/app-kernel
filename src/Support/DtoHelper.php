<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Provides utility methods for handling Data Transfer Objects (DTOs).
 *
 * This class offers methods to convert various objects, such as exceptions,
 * into data structures suitable for further serialization, logging, or debugging.
 * It ensures sensitive information is sanitized and provides additional tools
 * for customization and optimization of data output.
 */
abstract readonly class DtoHelper extends \Charcoal\Base\Support\Helpers\DtoHelper
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
}