<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel;

use Charcoal\Apps\Kernel\Db\Databases;
use Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface;
use Charcoal\Apps\Kernel\Errors\NullErrorLogger;
use Charcoal\Apps\Kernel\Modules\ModulesRegistry;
use Charcoal\Filesystem\Directory;

/**
 * Class AbstractApp
 * @package Charcoal\Apps\Kernel
 */
abstract class AbstractApp
{
    /**
     * @param \Charcoal\Filesystem\Directory $rootDirectory
     * @param string $name
     * @param string ...$childDirs
     * @return static
     */
    public static function Load(Directory $rootDirectory, string $name, array $childDirs = []): static
    {
        $app = unserialize(file_get_contents($rootDirectory->pathToChild(
            implode(DIRECTORY_SEPARATOR, $childDirs) . DIRECTORY_SEPARATOR .
            "charcoalAppBuild_" . $name . ".bin", false)));
        if (!$app instanceof AbstractApp) {
            throw new \RuntimeException('Cannot restore charcoal app');
        }

        return $app;
    }

    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     * @param \Charcoal\Filesystem\Directory $directory
     * @param string $name
     * @return void
     */
    public static function CreateBuild(AbstractApp $app, Directory $directory, string $name): void
    {
        if (!file_put_contents(
            $directory->pathToChild("charcoalAppBuild_" . $name . ".bin", false),
            serialize($app)
        )) {
            throw new \LogicException('Failed to create charcoal application build');
        }
    }

    public readonly AppKernel $kernel;
    public readonly ModulesRegistry $modules;
    public readonly Ciphers $ciphers;
    public readonly IO $io;
    public readonly Lifecycle $lifecycle;

    /**
     * @param \Charcoal\Filesystem\Directory $rootDirectory
     * @param \Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface $errorLogger
     * @param string $kernelClass
     * @param string $configClass
     * @param string $dirClass
     * @param string $dbClass
     * @param string $ciphersClass
     * @param string $ioClass
     */
    public function __construct(
        Directory            $rootDirectory,
        ErrorLoggerInterface $errorLogger = new NullErrorLogger(),
        string               $kernelClass = AppKernel::class,
        string               $configClass = Config::class,
        string               $dirClass = Directories::class,
        string               $dbClass = Databases::class,
        string               $ciphersClass = Ciphers::class,
        string               $ioClass = IO::class,
    )
    {
        $this->lifecycle = new Lifecycle();
        $this->kernel = new $kernelClass($rootDirectory, $errorLogger, $configClass, $dirClass, $dbClass);
        $this->lifecycle->log("New kernel instantiated");
        $this->modules = new ModulesRegistry();
        $this->io = new $ioClass($this);
        $this->ciphers = new $ciphersClass($this);
        $this->lifecycle->log("New abstract app instantiated");
    }

    /**
     * @return void
     */
    public function bootstrap(): void
    {
        /** @var \Charcoal\Apps\Kernel\Modules\AbstractModule $module */
        foreach ($this->modules as $module) {
            $module->bootstrap($this);
        }
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            "kernel" => $this->kernel,
            "modules" => $this->modules,
            "io" => $this->io,
            "lifecycle" => null
        ];
    }

    /**
     * @param array $object
     * @return void
     */
    public function __unserialize(array $object): void
    {
        $this->kernel = $object["kernel"];
        $this->modules = $object["modules"];
        $this->io = $object["io"];
        $this->lifecycle = new Lifecycle();
        $this->lifecycle->log("Restore app states successful");
    }

    /**
     * @param string|\Throwable $error
     * @param int $level
     * @param int $fileLineBacktraceIndex
     * @return void
     */
    public function triggerError(
        string|\Throwable $error,
        int               $level = E_USER_NOTICE,
        int               $fileLineBacktraceIndex = 2
    ): void
    {
        $this->kernel->errors->trigger($error, $level, $fileLineBacktraceIndex);
    }
}
