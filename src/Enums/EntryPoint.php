<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Class EntryPoint
 * @package Charcoal\App\Kernel\Enums
 */
enum EntryPoint
{
    case Http;
    case Cli;
}