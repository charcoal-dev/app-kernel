<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Interfaces\Cli;

use Charcoal\Base\Support\ObjectHelper;

/**
 * Class AbstractCliScript
 * @package Charcoal\App\Kernel\Interfaces\Cli
 * @property AppCliHandler $cli
 */
abstract class AbstractCliScript extends \Charcoal\CLI\AbstractCliScript
{
    public readonly CliScriptConfig $config;
    public readonly string $scriptClassname;

    /**
     * @param AppCliHandler $cli
     */
    public function __construct(AppCliHandler $cli)
    {
        parent::__construct($cli);
        $this->config = new CliScriptConfig();
        $this->scriptClassname = ObjectHelper::baseClassName(static::class);

        if (!$this->cli->app->errors->hasHandlersSet()) {
            throw new \LogicException(ObjectHelper::baseClassName($this->cli->app::class) .
                " error handlers not set; Cannot proceed to CLI interface");
        }
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