<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

/**
 * Represents the Server API (SAPI) type that is currently running.
 * - Http: Represents the HTTP Server API.
 * - Cli: Represents the Command Line Interface (CLI) Server API.
 */
enum SapiEnum
{
    case Http;
    case Cli;
}