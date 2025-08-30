<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support;

/**
 * Provides utility methods for working with file paths.
 */
abstract readonly class PathHelper
{
    /**
     * Extracts the last specified number of segments from a given file path.
     */
    public static function takeLastParts(string $path, int $parts = 2): string
    {
        $broken = explode(DIRECTORY_SEPARATOR, $path);
        return count($broken) > $parts ? ".." . DIRECTORY_SEPARATOR .
            implode(DIRECTORY_SEPARATOR, array_slice($broken, -1 * $parts)) : $path;
    }
}