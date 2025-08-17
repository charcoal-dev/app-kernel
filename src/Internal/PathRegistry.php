<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Filesystem\Exceptions\InvalidPathException;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\FilePath;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Class PathRegistry
 * @package Charcoal\App\Kernel\Internal
 */
readonly class PathRegistry implements AppServiceInterface
{
    public function __construct(public DirectoryPath|PathInfo $root)
    {
    }

    /**
     * @param string $relative
     * @param bool $isDirectory
     * @param bool $read
     * @param bool $write
     * @param bool $execute
     * @return DirectoryPath|FilePath
     * @api
     */
    protected function getValidatedPathSnapshot(
        string $relative,
        bool   $isDirectory,
        bool   $read,
        bool   $write,
        bool   $execute,
    ): DirectoryPath|FilePath
    {
        if (strlen($relative) < 2) {
            throw new \RuntimeException("Invalid relative path for PathRegistry");
        }

        try {
            $path = $isDirectory ? new DirectoryPath($this->root->absolute . DIRECTORY_SEPARATOR . $relative) :
                new FilePath($this->root->absolute . DIRECTORY_SEPARATOR . $relative);
        } catch (InvalidPathException $e) {
            throw new \RuntimeException($e->getMessage(), previous: $e);
        }

        $exception = match (true) {
            $read && !$path->readable => "is not readable",
            $write && !$path->writable => "is not writable",
            $execute && !$path->executable => "is not executable",
            default => null,
        };

        if ($exception) {
            throw new \RuntimeException($this->getExceptionPrefix($relative, $isDirectory) . $exception);
        }

        return $path;
    }

    /**
     * @param string $relative
     * @param bool $isDirectory
     * @return string
     * @internal
     */
    protected function getExceptionPrefix(
        string $relative,
        bool   $isDirectory
    ): string
    {
        return sprintf('Path to %s "/%s" ', $isDirectory ? "directory" : "file", basename($relative));
    }
}