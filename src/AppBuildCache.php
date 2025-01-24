<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\Filesystem\Directory;

/**
 * Class AppBuildCache
 * @package Charcoal\App\Kernel
 */
abstract class AppBuildCache
{
    /**
     * @param Directory $rootDirectory
     * @param string $suffix
     * @param array $childDirs
     * @return static
     */
    public static function Load(Directory $rootDirectory, string $suffix, array $childDirs = []): static
    {
        $buildFilepath = $rootDirectory->pathToChild(
            implode(DIRECTORY_SEPARATOR, $childDirs) . DIRECTORY_SEPARATOR .
            "charcoalAppBuild_" . $suffix . ".bin", false);
        if (!is_file($buildFilepath) || !is_readable($buildFilepath)) {
            throw new \RuntimeException(sprintf('Charcoal app build file "%s" not found/readable', basename($buildFilepath)));
        }

        $app = unserialize(file_get_contents($buildFilepath));
        if (!$app instanceof static) {
            throw new \RuntimeException("Cannot restore charcoal app");
        }

        return $app;
    }

    /**
     * @param AppKernel $app
     * @param Directory $directory
     * @param string $suffix
     * @return void
     */
    public static function CreateBuild(AppKernel $app, Directory $directory, string $suffix): void
    {
        if (!file_put_contents($directory->pathToChild("charcoalAppBuild_" . $suffix . ".bin", false), serialize($app))) {
            throw new \LogicException("Failed to create charcoal application build");
        }
    }
}