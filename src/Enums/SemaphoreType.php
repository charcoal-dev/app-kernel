<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Represents the different types of semaphore mechanisms that can be used.
 * @api
 */
enum SemaphoreType
{
    case Filesystem;
}