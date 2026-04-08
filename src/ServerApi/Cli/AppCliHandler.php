<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Errors\Loggers\RuntimeErrorLog;
use Charcoal\App\Kernel\ServerApi\Cli\Traits\ConsoleEventsTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Objects\Traits\NotSerializableTrait;
use Charcoal\Cli\Console;
use Charcoal\Cli\Enums\ExecutionState;
use Charcoal\Cli\Events\State\RuntimeStatusChange;
use Charcoal\Cli\Output\StdoutPrinter;

/**
 * Handles CLI interactions for the application. Manages the creation and execution
 * of CLI scripts and processes various internal application states during execution.
 * @property AppCliScript $execScriptObject
 */
class AppCliHandler extends Console
{
    public readonly StdoutPrinter $stdout;
    public readonly RuntimeErrorLog $errorLog;

    use NotCloneableTrait;
    use NoDumpTrait;
    use NotSerializableTrait;

    use ConsoleEventsTrait;

    public function __construct(
        public readonly AbstractApp $app,
        string                      $scriptsNamespace,
        array                       $args,
        ?string                     $defaultScriptName,
    )
    {
        $this->errorLog = new RuntimeErrorLog();
        $this->app->errors->subscribe($this->errorLog);

        parent::__construct($scriptsNamespace, $args, $defaultScriptName);
        $this->stdout = new StdoutPrinter();
        $this->addOutputHandler($this->stdout);

        $this->events->subscribe()->listen(RuntimeStatusChange::class, function (RuntimeStatusChange $event) {
            match ($event->state) {
                ExecutionState::Initializing => function () {
                    if (!extension_loaded("pcntl")) {
                        $this->print("{red}{b}PCNTL{/}{red} extension is not loaded.");
                    }
                },
                ExecutionState::Failed => $this->onScriptInitFailed($event->exception),
                ExecutionState::Ready => $this->onScriptReady(),
                ExecutionState::Finished => $this->onScriptCompleted(),
                default => null,
            };
        });
    }
}