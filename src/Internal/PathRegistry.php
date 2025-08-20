<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Diagnostics\Events\BuildStageEvents;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Filesystem\Exceptions\FilesystemException;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\FilePath;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Represents a registry for managing and validating file or directory paths based on a specified root path.
 * Provides functionality for declaring paths and retrieving validated path instances with specific
 * access permissions.
 */
readonly class PathRegistry implements AppServiceInterface
{
    /**
     * Use "declarePaths" method as constructor.
     */
    final public function __construct(
        protected AppEnv              $env,
        public DirectoryPath|PathInfo $root,
    )
    {
    }

    final public function acceptPathsDeclaration(AbstractApp $app): void
    {
        $this->declarePaths();
        $app->diagnostics->buildStageStream(BuildStageEvents::PathRegistryOn);
    }

    /**
     * Declare paths here, invoked internally by application when error handlers are registered.
     * This method is intended to be overridden by application classes and acts like a constructor.
     * @api
     */
    public function declarePaths(): void
    {
    }

    /**
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
            $path = $isDirectory ? $this->root->join($relative)->isDirectory() :
                $this->root->join($relative)->isFile();
        } catch (FilesystemException $e) {
            throw new \RuntimeException("[" . $relative . "]: " . $e->getMessage(), previous: $e);
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
     * @internal
     */
    private function getExceptionPrefix(
        string $relative,
        bool   $isDirectory
    ): string
    {
        return sprintf('Path to %s "/%s" ', $isDirectory ? "directory" : "file", basename($relative));
    }
}