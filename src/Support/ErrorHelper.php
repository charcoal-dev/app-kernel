<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * This class provides helper methods for handling and formatting error information.
 * It extends the base ErrorHelper class to include additional functionality.
 */
abstract readonly class ErrorHelper extends \Charcoal\Base\Support\Helpers\ErrorHelper
{
    /**
     * Converts and sanitizes \Throwable object to a DTO
     * @api
     */
    public static function getExceptionDto(
        \Throwable $t,
        bool       $previous = true,
        bool       $trace = true,
        int        $pathOffset = 0
    ): array
    {
        return DtoHelper::getExceptionObject($t, $previous, $trace, $pathOffset);
    }

    /**
     * Produces universal error DTO.
     */
    public static function getErrorDto(\Throwable|ErrorEntry $error, bool $trace = true): array
    {
        $dto = [];
        if ($error instanceof ErrorEntry) {
            $dto["class"] = $error->level;
            $dto["file"] = $error->filepath;
            $dto["line"] = $error->line;
            $dto["code"] = $error->errno;
            $dto["message"] = $error->message;
            $dto["trace"] = $error->backtrace;
        }

        if ($error instanceof \Throwable) {
            $dto = self::getExceptionDto($error, true, true);
        }

        if (!$trace) {
            unset($dto["trace"]);
        }

        return $dto;
    }

    /**
     * @return string[]
     */
    public static function errorDtoTemplate(): array
    {
        return [
            // [0]: Backtrace line template:
            "\x20\x20\x20\x20\x20\x20\x20{cyan}[%1\$s, @%2\$s{cyan}]{/}",
            // [1]: Previous boundary
            "{yellow}Next:{/} {grey}%s{/}",
            // [...]: Error DTO template:
            "{magenta}[@{:datetime:}]{/}",
            "{red}[@{:class:}][{yellow}#@{:code:}{red}]{/}",
            "@{:message:}",
            "{yellow}Trace:{/}\x20{blue}[@{:file:}, {cyan}@{:line:}{blue}]{/}"];
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