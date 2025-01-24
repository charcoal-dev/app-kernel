<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel;

use Charcoal\Filesystem\Directory;

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
}