<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\ServerApi;

use Charcoal\Contracts\Sapi\SapiType;

/**
 * Defines the contract for a server API enumeration interface.
 * This interface provides a method to retrieve the server API type.
 */
interface ServerApiEnumInterface extends \UnitEnum
{
    public function type(): SapiType;
}