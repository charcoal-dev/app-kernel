<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli;

/**
 * Provides options to enable or disable the display of headers,
 * application class banners, script names, and triggered errors
 * during script execution.
 */
class CliScriptConfig
{
    public bool $displayHeaders = true;
    public bool $displayAppClassBanner = true;
    public bool $displayScriptName = true;
    public bool $displayTriggeredErrors = true;
}