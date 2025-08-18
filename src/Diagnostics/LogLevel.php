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
    case Debug = 0;
    case Info = 1;
    case Warning = 2;
    case Error = 3;
    case Critical = 4;
}