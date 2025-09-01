<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\Filesystem\Enums\PathType;
use Charcoal\Filesystem\Exceptions\FilesystemException;
use Charcoal\Filesystem\Node\DirectoryNode;
use Charcoal\Filesystem\Node\FileNode;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Provides functionality to load and create serialized application builds.
 * @internal
 */
abstract readonly class AppSerializable
{
    /**
     * @param AppEnv $env
     * @param DirectoryNode $root
     * @param array $dirs
     * @return static
     * @throws \Exception
     * @api
     */
    public static function Load(AppEnv $env, DirectoryNode $root, array $dirs = []): static
    {
        $filepath = implode(DIRECTORY_SEPARATOR, $dirs) . DIRECTORY_SEPARATOR .
            "charcoalAppSerialized_" . $env->value . ".bin";

        $serialized = $root->childPathInfo($filepath);
        if ($serialized->type !== PathType::File || !$serialized->readable) {
            throw new \RuntimeException(
                sprintf('Charcoal app build file "%s" not found/readable',
                    basename($serialized->absolute)));
        }

        error_clear_last();
        $app = @unserialize($root->read($filepath, true));
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
     * @return PathInfo
     * @api
     */
    public static function CreateBuild(AbstractApp $app, DirectoryNode $root, array $dirs = []): PathInfo
    {
        try {
            $filepath = implode(DIRECTORY_SEPARATOR, $dirs) . DIRECTORY_SEPARATOR .
                "charcoalAppSerialized_" . $app->context->env->value . ".bin";
            $serialized = $root->childPathInfo($filepath);
            if ($serialized->type === PathType::Missing) {
                $root->touch($filepath, true);
                $serialized = $root->childPathInfo($filepath);
            }

            $serialized = new FileNode($serialized);
            $serialized->write(serialize($app), false, true);
            return $serialized->path;
        } catch (FilesystemException $e) {
            throw new \LogicException("Failed to create charcoal application build", previous: $e);
        }
    }
}