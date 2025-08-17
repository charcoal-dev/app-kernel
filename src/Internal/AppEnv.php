<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

/**
 * Class AppEnv
 * @package Charcoal\App\Kernel\Internal
 */
enum AppEnv: string
{
    case Dev = "dev";
    case Prod = "prod";
}