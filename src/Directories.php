<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\Filesystem\Directory;
use Charcoal\Filesystem\Exception\FilesystemException;

/**
 * Class Directories
 * @package Charcoal\App\Kernel
 */
class Directories
{
    public function __construct(
        public readonly Directory $root
    )
    {
    }

    /**
     * @param string $dirPath
     * @param bool $checkReadable
     * @param bool $checkWritable
     * @return Directory
     */
    protected function validateDirectory(string $dirPath, bool $checkReadable, bool $checkWritable): Directory
    {
        try {
            $dir = $this->root->getDirectory($dirPath, createIfNotExists: false);
            if ($checkReadable && !$dir->isReadable()) {
                throw new \RuntimeException(sprintf('Directory "%s" is not readable', $dirPath));
            }

            if ($checkWritable && !$dir->isWritable()) {
                throw new \RuntimeException(sprintf('Directory "%s" is not writable', $dirPath));
            }

            return $dir;
        } catch (FilesystemException $e) {
            throw new \RuntimeException(sprintf('Directory "%s" error %s', $dirPath, $e->error->name));
        }
    }
}