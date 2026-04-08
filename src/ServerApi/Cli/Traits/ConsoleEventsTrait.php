<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli\Traits;

use Charcoal\Base\Objects\ObjectHelper;
use Charcoal\Cli\Script\Exceptions\ScriptNotFoundException;

/**
 * Trait ConsoleEventsTrait
 * @package Charcoal\App\Kernel\ServerApi\Cli\Traits
 */
trait ConsoleEventsTrait
{
    use ConsoleDecoratorTrait;

    /**
     * @param \Throwable|null $t
     * @return void
     */
    private function onScriptInitFailed(?\Throwable $t = null): void
    {
        $this->printAppHeaders();
        $this->printAppClassBanner();

        if ($t instanceof ScriptNotFoundException) {
            if ($t->scriptFqcn) {
                $this->print(sprintf("CLI script: {red}{invert} %s {/}", $t->scriptFqcn));
            }

            $this->print($t->getMessage());
            $this->print("");
        }
    }

    /**
     * @return void
     */
    private function onScriptReady(): void
    {
        if ($this->execScriptObject->config->displayHeaders) {
            $this->printAppHeaders();
        }

        if ($this->execScriptObject->config->displayAppClassBanner) {
            $this->printAppClassBanner();
        }

        if ($this->execScriptObject->config->displayScriptName) {
            $this->inline(sprintf("CLI script {green}{invert} %s {/} loaded",
                ObjectHelper::baseClassName($this->execClassname)));

            $this->repeatChar(".", 3, 100, true);
            $this->print("");
        }
    }

    /**
     * @return void
     */
    private function onScriptCompleted(): void
    {
        $displayErrors = $this->execScriptObject->config->displayCaughtErrors ?? true;
        if ($displayErrors) {
            $this->printErrors(0, false, true);
        }
    }
}