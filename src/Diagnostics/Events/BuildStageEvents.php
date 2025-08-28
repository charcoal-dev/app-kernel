<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics\Events;

/**
 * Enumeration representing the various stages of the build process
 * while implementing the context for diagnostic events.
 */
enum BuildStageEvents implements DiagnosticsEventsContext
{
    case DiagnosticsReady;
    case ErrorServiceStarted;
    case ErrorHandlersOn;
    case PathRegistryOn;
    case ConfigLoaded;
    case ServicesReady;
    case DomainModulesLoaded;
    case Ready;
    case Bootstrapped;
}