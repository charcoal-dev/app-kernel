<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\EntryPoint;

use Charcoal\App\Kernel\Enums\SapiEnum;

/**
 * Represents an enumeration type for entry points.
 * This interface extends the native PHP UnitEnum, allowing
 * for the implementation of enumerations adhering to specific
 * entry-point-related use cases within the application.
 */
interface EntryPointEnumInterface extends \UnitEnum
{
    public function getType(): SapiEnum;
}