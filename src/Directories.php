<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\Filesystem\Directory;

/**
 * Class Directories
 * @package Charcoal\App\Kernel
 */
readonly class Directories
{
    /**
     * @param Directory $root
     */
    public function __construct(
        public Directory $root
    )
    {
    }

    /**
     * @throws \Charcoal\Filesystem\Exceptions\FilesystemException
     * @api
     */
    protected function validateDirectory(string $dirPath, bool $readable, bool $writable): Directory
    {
        $dir = $this->root->getDirectory($dirPath, createIfNotExists: false);
        if ($readable && !$dir->isReadable()) {
            throw new \RuntimeException(sprintf('Directory "%s" is not readable', $dirPath));
        }

        if ($writable && !$dir->isWritable()) {
            throw new \RuntimeException(sprintf('Directory "%s" is not writable', $dirPath));
        }

        return $dir;
    }
}