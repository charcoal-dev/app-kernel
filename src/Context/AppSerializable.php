<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Context;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Internal\AppEnv;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\Filesystem\Enums\PathType;
use Charcoal\Filesystem\Exceptions\FilesystemException;
use Charcoal\Filesystem\Node\DirectoryNode;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Class AppSerializable
 * @package Charcoal\App\Kernel\Context
 * @internal
 */
abstract class AppSerializable
{
    /**
     * @param AppEnv $env
     * @param DirectoryNode $root
     * @param array $dirs
     * @return static
     * @throws \Exception
     */
    public static function Load(AppEnv $env, DirectoryNode $root, array $dirs = []): static
    {
        $serialized = static::appSerializedStatesFilepath($env, $root, $dirs);
        if ($serialized->type !== PathType::File || !$serialized->readable) {
            throw new \RuntimeException(
                sprintf('Charcoal app build file "%s" not found/readable',
                    basename($serialized->absolute)));
        }

        error_clear_last();
        $app = unserialize($root->read($serialized->absolute, true));
        if (!$app instanceof static) {
            throw new \RuntimeException("Cannot restore charcoal app",
                previous: ErrorHelper::lastErrorToRuntimeException());
        }

        return $app;
    }

    /**
     * @param AbstractApp $app
     * @param DirectoryNode $root
     * @param array $dirs
     * @return void
     */
    public static function CreateBuild(AbstractApp $app, DirectoryNode $root, array $dirs = []): void
    {
        $filepath = static::appSerializedStatesFilepath($app->env, $root, $dirs);
        error_clear_last();
        if (!@file_put_contents($filepath->absolute, serialize($app))) {
            throw new \LogicException("Failed to create charcoal application build");
        }
    }

    /**
     * @param AppEnv $env
     * @param DirectoryNode $root
     * @param array $dirs
     * @return PathInfo
     */
    protected static function appSerializedStatesFilepath(
        AppEnv        $env,
        DirectoryNode $root,
        array         $dirs = []
    ): PathInfo
    {
        try {
            return $root->childPathInfo(
                implode(DIRECTORY_SEPARATOR, $dirs) . DIRECTORY_SEPARATOR .
                "charcoalAppSerialized_" . $env->value . ".bin", true);
        } catch (FilesystemException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @internal
     */
    protected function registerModuleManifest(AppBuildContextInterface $context, AppBuildStage $app): array
    {
        $modules = $context->declareModules($app)->getIncluded();
        $moduleClasses = [];
        $moduleProperties = [];
        foreach ($modules as $property => $instance) {
            $this->$property = $instance;
            $moduleClasses[$instance::class] = $property;
            $moduleProperties[] = $property;
        }

        return [$moduleClasses, $moduleProperties];
    }
}