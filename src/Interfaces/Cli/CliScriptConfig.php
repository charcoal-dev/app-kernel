<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Interfaces\Cli;

/**
 * Class CliScriptConfig
 * @package Charcoal\App\Kernel\Interfaces\Cli
 */
class CliScriptConfig
{
    public bool $displayHeaders = true;
    public bool $displayAppClassBanner = true;
    public bool $displayScriptName = true;
    public bool $displayTriggeredErrors = true;
}