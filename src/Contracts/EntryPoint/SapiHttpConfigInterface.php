<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\EntryPoint;

/**
 * Represents an interface for configuring SAPI HTTP settings.
 * This interface defines the contract for classes that manage
 * configuration related to the Server API (SAPI) HTTP context.
 */
interface SapiHttpConfigInterface extends SapiConfigInterface
{
}