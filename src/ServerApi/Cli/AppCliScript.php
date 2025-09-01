<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli;

use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Cli\Enums\ExecutionState;

/**
 * This abstract class extends the capabilities of Charcoal's AbstractCliScript
 * to include additional configuration and behavior specific to the application's
 * CLI implementation. It ensures that error handlers are properly set before
 * proceeding with the CLI operations.
 * @property AppCliHandler $cli
 * @api
 */
abstract class AppCliScript extends \Charcoal\Cli\Script\AbstractCliScript
{
    public readonly CliScriptConfig $config;
    public readonly string $scriptClassname;

    /**
     * @param AppCliHandler $cli
     * @param ExecutionState $initialState
     */
    public function __construct(
        AppCliHandler  $cli,
        ExecutionState $initialState = ExecutionState::STARTED
    )
    {
        parent::__construct($cli, $initialState);
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