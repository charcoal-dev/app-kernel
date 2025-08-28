<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

use Charcoal\App\Kernel\Enums\SapiType;

/**
 * Represents an enumeration type for entry points.
 * This interface extends the native PHP UnitEnum, allowing
 * for the implementation of enumerations adhering to specific
 * entry-point-related use cases within the application.
 */
interface SapiEnumInterface extends \UnitEnum
{
    public function getType(): SapiType;
}