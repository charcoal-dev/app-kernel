<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entrypoints\Cli;

/**
 * Class CliScriptConfig
 * @package Charcoal\App\Kernel\Entrypoints\Cli
 */
class CliScriptConfig
{
    public bool $displayHeaders = true;
    public bool $displayLoadedClassname = true;
    public bool $displayTriggeredErrors = true;
}