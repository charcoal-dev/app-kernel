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
 * Represents a contract for enumerations that define the scope of semaphore.
 * Extends the functionality provided by the ConfigEnumInterface.
 */
interface SemaphoreScopeEnumInterface extends ConfigEnumInterface
{
    public function getType(): SemaphoreType;
}