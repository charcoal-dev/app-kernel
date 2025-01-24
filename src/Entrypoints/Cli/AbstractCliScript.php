<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entrypoints\Cli;

use Charcoal\CLI\CLI;
use Charcoal\OOP\OOP;

/**
 * Class AbstractCliScript
 * @package Charcoal\App\Kernel\Entrypoints\Cli
 */
abstract class AbstractCliScript extends \Charcoal\CLI\AbstractCliScript
{
    public readonly CliScriptConfig $config;
    public readonly string $scriptClassname;

    /**
     * @param CLI $cli
     */
    public function __construct(CLI $cli)
    {
        parent::__construct($cli);
        $this->config = new CliScriptConfig();
        $this->scriptClassname = OOP::baseClassName(static::class);
    }

    /**
     * @param int $sigId
     * @return void
     */
    public function onSignalCloseCallback(int $sigId): void
    {
        if (!extension_loaded("pcntl")) {
            return;
        }

        $this->print("");
        $this->print(sprintf("{red}Closing due to process-control signal {invert} %s {/}{red} received{/}",
            match ($sigId) {
                SIGTERM => "SIGTERM",
                SIGQUIT => "SIGQUIT",
                SIGINT => "SIGINT",
                SIGHUP => "SIGHUP",
                SIGALRM => "SIGALRM",
                default => strval($sigId)
            }));
    }
}