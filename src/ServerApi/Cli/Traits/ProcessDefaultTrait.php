<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli\Traits;

use Charcoal\App\Kernel\ServerApi\Cli\CliScriptConfig;
use Charcoal\Base\Objects\ObjectHelper;

/**
 * Provides default functionality and configuration management
 * for CLI-based scripts. Handles initialization of default settings
 * and reacts to process-control signals.
 */
trait ProcessDefaultTrait
{
    public readonly CliScriptConfig $config;

    /**
     * @return void
     */
    private function initializeCliDefaults(): void
    {
        $this->config = new CliScriptConfig();

        // Ensure error handlers
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