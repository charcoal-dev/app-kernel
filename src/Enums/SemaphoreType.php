<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Enumeration representing semaphore types.
 * This enum defines the different types of semaphores available for use,
 * distinguishing between private and shared filesystem semaphores.
 */
enum SemaphoreType
{
    case LFS;
    case Redis;
}