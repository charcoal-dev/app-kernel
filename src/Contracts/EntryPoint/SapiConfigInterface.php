<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\EntryPoint;

use Charcoal\App\Kernel\Contracts\Enums\SapiEnumInterface;

/**
 * Interface defining the configuration contract for SAPI (Server API).
 * @property-read SapiEnumInterface $interface
 */
interface SapiConfigInterface
{
}