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

use Charcoal\Filesystem\Directory;
use Charcoal\Filesystem\Exception\FilesystemException;

/**
 * Class Directories
 * @package Charcoal\Apps\Kernel
 */
class Directories
{
    public readonly Directory $config;
    public readonly Directory $log;
    public readonly Directory $semaphore;
    public readonly Directory $storage;
    public readonly Directory $tmp;

    /**
     * @param \Charcoal\Filesystem\Directory $root
     */
    public function __construct(public readonly Directory $root)
    {
        $this->config = $this->registerDirectory("/config", true, false);
        $this->log = $this->registerDirectory("/log", true, true);
        $this->tmp = $this->registerDirectory("/tmp", true, true);
        $this->semaphore = $this->registerDirectory("/tmp/semaphore", true, true);
        $this->storage = $this->registerDirectory("/storage", true, false);
    }

    /**
     * @param string $dirPath
     * @param bool $checkReadable
     * @param bool $checkWritable
     * @return \Charcoal\Filesystem\Directory
     */
    protected function registerDirectory(string $dirPath, bool $checkReadable, bool $checkWritable): Directory
    {
        try {
            $dir = $this->root->getDirectory($dirPath, createIfNotExists: false);
            if ($checkReadable && !$dir->isReadable()) {
                throw new \DomainException(sprintf('Directory "%s" is not readable', $dirPath));
            }

            if ($checkWritable && !$dir->isWritable()) {
                throw new \DomainException(sprintf('Directory "%s" is not writable', $dirPath));
            }

            return $dir;
        } catch (FilesystemException $e) {
            throw new \DomainException(sprintf('Directory "%s" error %s', $dirPath, $e->error->name));
        }
    }
}

