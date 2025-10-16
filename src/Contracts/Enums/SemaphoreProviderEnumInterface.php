<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\App\Kernel\Internal\Config\ConfigEnumInterface;

/**
 * Represents an enumeration for semaphore providers with a specific type.
 * Extends the ConfigEnumInterface to standardize configuration management for semaphore-based implementations.
 */
interface SemaphoreProviderEnumInterface extends ConfigEnumInterface
{
    public function getType(): SemaphoreType;
}