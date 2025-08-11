<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Interfaces\Cli;

use Charcoal\App\Kernel\AppBuild;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\Base\Support\ObjectHelper;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Base\Traits\NotSerializableTrait;
use Charcoal\CLI\Banners;
use Charcoal\CLI\CLI;
use Charcoal\CLI\Console\StdoutPrinter;
use Composer\InstalledVersions;

/**
 * Class AppCliHandler
 * @package Charcoal\App\Kernel\Interfaces\Cli
 */
class AppCliHandler extends CLI
{
    public readonly StdoutPrinter $stdout;

    use NotCloneableTrait;
    use NoDumpTrait;
    use NotSerializableTrait;

    /**
     * @param AppBuild $app
     * @param string $scriptsNamespace
     * @param array $args
     * @param string|null $defaultScriptName
     */
    public function __construct(
        public readonly AppBuild $app,
        string                   $scriptsNamespace,
        array                    $args,
        ?string                  $defaultScriptName,
    )
    {
        parent::__construct($scriptsNamespace, $args, $defaultScriptName);
        $this->stdout = new StdoutPrinter();
        $this->addOutputHandler($this->stdout);

        // Event: ScriptNotFound
        $this->events->scriptNotFound()->listen(function (self $cli, string $scriptClassname) {
            $this->printAppHeaders();
            $this->printAppClassBanner();
            $cli->print(sprintf("CLI script {red}{invert} %s {/} not found",
                ObjectHelper::baseClassName($scriptClassname)));
            $cli->print("");
        });

        // Event: ScriptLoaded
        $this->events->scriptLoaded()->listen(function (self $cli) {
            // Headers & Loaded Script Name
            if ($this->execScriptObject->config->displayHeaders) {
                $this->printAppHeaders();
            }

            if ($this->execScriptObject->config->displayAppClassBanner) {
                $this->printAppClassBanner();
            }

            if ($this->execScriptObject->config->displayScriptName) {
                $cli->inline(sprintf("CLI script {green}{invert} %s {/} loaded",
                    ObjectHelper::baseClassName($this->execClassname)));

                $cli->repeatChar(".", 3, 100, true);
                $cli->print("");
            }
        });

        // Event: BeforeExec
        $this->events->beforeExec()->listen(function (self $cli) {
            if (!extension_loaded("pcntl")) {
                $cli->print("{red}{b}PCNTL{/}{red} extension is not loaded.");
            }
        });

        // Event: AfterExec
        $this->events->afterExec()->listen(function (self $cli, bool $isSuccess) {
            if ($isSuccess) {
                $displayErrors = $this->execScriptObject->options->displayTriggeredErrors ?? true;
                if ($displayErrors) {
                    $this->printErrors(0, false, true);
                }
            }
        });
    }

    /**
     * @param int $tabIndex
     * @param bool $displayCompact
     * @param bool $displayNoErrorsCaught
     * @return void
     */
    public function printErrors(int $tabIndex = 0, bool $displayCompact = true, bool $displayNoErrorsCaught = false): void
    {
        $tabs = str_repeat("\t", $tabIndex);
        $errorLog = $this->app->errors;
        $errorsCount = $errorLog->count();
        if (!$errorsCount) {
            if ($displayNoErrorsCaught) {
                $this->print("{grey}No errors caught!{/}");
            }

            return;
        }

        $this->print("");
        $this->print($tabs . sprintf("{red}{b}%d{/}{red} errors caught!{/}", $errorsCount));
        /** @var ErrorEntry $errorMsg */
        foreach ($errorLog as $errorMsg) {
            if ($displayCompact) {
                $this->print($tabs . sprintf('[{red}{b}%s{/}]{red} %s{/}', $errorMsg->levelStr, $errorMsg->message));
                $this->print($tabs . sprintf('⮤ in {magenta}%s{/} on line {magenta}%d{/}', $errorMsg->filePath, $errorMsg->line));
            } else {
                $this->print($tabs . sprintf('{grey}│  ┌ {/}{yellow}Type:{/} {magenta}%s{/}', strtoupper($errorMsg->levelStr)));
                $this->print($tabs . sprintf('{grey}├──┼ {/}{yellow}Message:{/} %s', $errorMsg->message));
                $this->print($tabs . sprintf("{grey}│  ├ {/}{yellow}File:{/} {cyan}%s{/}", $errorMsg->filePath));
                $this->print($tabs . sprintf("{grey}│  └ {/}{yellow}Line:{/} %d", $errorMsg->line));
                $this->print($tabs . "{grey}│{/}");
            }
        }

        $this->print("");
    }

    /**
     * @return void
     */
    public function printAppHeaders(): void
    {
        $this->print(sprintf("{yellow}{invert}Charcoal App Kernel{/} {grey}%s{/}",
            InstalledVersions::getPrettyVersion("charcoal-dev/app-kernel")), 200);
        $this->print(sprintf("{cyan}{invert}Charcoal CLI{/} {grey}%s{/}",
            InstalledVersions::getPrettyVersion("charcoal-dev/cli")), 200);
        $this->print("");
    }

    /**
     * @return void
     */
    public function printAppClassBanner(): void
    {
        $this->repeatChar("~", 5, 100, true);
        foreach (Banners::Digital(ObjectHelper::baseClassName($this->app::class))->lines as $line) {
            $this->print("{magenta}{invert}" . $line . "{/}");
        }

        $this->repeatChar("~", 5, 100, true);
        $this->print("");
    }
}