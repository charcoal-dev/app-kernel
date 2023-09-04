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

namespace Charcoal\Tests\Apps\Objects;

use Charcoal\Apps\Kernel\AbstractApp;
use Charcoal\Apps\Kernel\AppKernel;
use Charcoal\Apps\Kernel\Config;
use Charcoal\Apps\Kernel\Db\Databases;
use Charcoal\Apps\Kernel\Directories;
use Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface;
use Charcoal\Apps\Kernel\Errors\NullErrorLogger;
use Charcoal\Apps\Kernel\IO;
use Charcoal\Filesystem\Directory;

/**
 * Class DemoApp
 * @package Charcoal\Tests\Apps\Objects
 */
class DemoApp extends AbstractApp
{
    public function __construct(
        Directory            $rootDirectory,
        ErrorLoggerInterface $errorLogger = new NullErrorLogger(),
        string               $kernelClass = AppKernel::class,
        string               $configClass = Config::class,
        string               $dirClass = Directories::class,
        string               $dbClass = Databases::class,
        string               $ioClass = IO::class
    )
    {
        parent::__construct($rootDirectory, $errorLogger, $kernelClass, $configClass, $dirClass, $dbClass, $ioClass);
        $this->modules->register("users", new UsersModule());
    }

    public function users(): UsersModule
    {
        /** @var \Charcoal\Tests\Apps\Objects\UsersModule */
        return $this->modules->get("users");
    }
}
