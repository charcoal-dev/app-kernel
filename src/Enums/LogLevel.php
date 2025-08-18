<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Class LogLevel
 * @package Charcoal\App\Kernel\Enums
 */
enum LogLevel: int
{
    case Verbose = 0;
    case Debug = 10;
    case Info = 20;
    case Notice = 30;
    case Warning = 40;
    case Error = 50;
    case Critical = 90;
}