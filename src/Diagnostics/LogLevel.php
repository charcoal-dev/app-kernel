<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

/**
 * Class LogLevel
 * @package Charcoal\App\Kernel\Diagnostics
 */
enum LogLevel: int
{
    case Verbose = 0;
    case Debug = 10;
    case Info = 20;
    case Warning = 30;
    case Error = 40;
    case Critical = 90;
}