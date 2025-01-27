<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Build;

use Charcoal\App\Kernel\AppKernel;
use Charcoal\Filesystem\Directory;

/**
 * Class AppBuildCache
 * @package Charcoal\App\Kernel\Build
 */
abstract class AppBuildCache
{
    protected readonly BuildMetadata $build;

    /**
     * @param Directory $rootDirectory
     * @param AppBuildEnum $build
     * @param array $childDirs
     * @return static
     */
    public static function Load(Directory $rootDirectory, AppBuildEnum $build, array $childDirs = []): static
    {
        $buildFilepath = $rootDirectory->pathToChild(
            implode(DIRECTORY_SEPARATOR, $childDirs) . DIRECTORY_SEPARATOR .
            "charcoalAppBuild_" . $build->getName() . ".bin", false);
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
     * @return void
     */
    public static function CreateBuild(AppKernel $app, Directory $directory): void
    {
        if (!file_put_contents($directory->pathToChild("charcoalAppBuild_" . $app->build->enum->getName() . ".bin", false), serialize($app))) {
            throw new \LogicException("Failed to create charcoal application build");
        }
    }
}