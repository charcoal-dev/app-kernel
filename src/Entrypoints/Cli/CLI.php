<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Entrypoints\Cli;

use Charcoal\Apps\Kernel\AbstractApp;
use Charcoal\CLI\Banners;
use Charcoal\CLI\Console\StdoutPrinter;
use Charcoal\Filesystem\Directory;
use Charcoal\OOP\OOP;
use Charcoal\OOP\Traits\NotSerializableTrait;
use Charcoal\Semaphore\Exception\SemaphoreLockException;
use Charcoal\Semaphore\Filesystem\FileLock;
use Composer\InstalledVersions;

/**
 * Class CLI
 * @package Charcoal\Apps\Kernel\Entrypoints\Cli
 */
class CLI extends \Charcoal\CLI\CLI
{
    public readonly StdoutPrinter $stdout;

    use NotSerializableTrait;

    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     * @param \Charcoal\Filesystem\Directory $scriptsDirectory
     * @param array $args
     */
    public function __construct(
        public readonly AbstractApp $app,
        Directory                   $scriptsDirectory,
        array                       $args
    )
    {
        parent::__construct($scriptsDirectory, $args);
        $this->stdout = new StdoutPrinter();
        $this->addOutputHandler($this->stdout);

        // Event: ScriptNotFound
        $this->events->scriptNotFound()->listen(function (self $cli, string $scriptClassname) {
            $this->printAppHeaders();
            $cli->print(sprintf("CLI script {red}{invert} %s {/} not found", OOP::baseClassName($scriptClassname)));
            $cli->print("");
        });

        // Event: ScriptLoaded
        $this->events->scriptLoaded()->listen(function (self $cli, AbstractCliScript $script) {
            // Headers & Loaded Script Name
            if ($script->options->displayHeaders) {
                $this->printAppHeaders();
            }

            if ($script->options->displayLoadedClassname) {
                $cli->inline(sprintf('CLI script {green}{invert} %s {/} loaded', OOP::baseClassName(get_class($script))));
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
        $this->events->afterExec()->listen(function (self $cli, bool $isSuccess, ?AbstractCliScript $script) {
            $displayErrors = $script?->options->displayTriggeredErrors ?? true;
            if ($displayErrors) {
                $this->printErrors(0, false, true);
            }
        });
    }

    /**
     * @param string $resourceId
     * @param bool $setAutoRelease
     * @return \Charcoal\Semaphore\Filesystem\FileLock
     * @throws \Charcoal\Semaphore\Exception\SemaphoreLockException
     */
    public function obtainSemaphoreLock(string $resourceId, bool $setAutoRelease): FileLock
    {
        $this->inline(sprintf("Obtaining semaphore lock for {yellow}{invert} %s {/} ... ", $resourceId));

        try {
            $lock = $this->app->kernel->semaphore->obtainLock($resourceId, null);
            $this->inline("{green}Success{/} {grey}[AutoRelease={/}");
            if ($setAutoRelease) {
                $lock->setAutoRelease();
                $this->print("{green}1{grey}]{/}");
            } else {
                $this->print("{red}0{grey}]{/}");
            }

            return $lock;
        } catch (SemaphoreLockException $e) {
            $this->print("{red}{invert} " . $e->error->name . " {/}");
            throw $e;
        }
    }

    /**
     * @param \Throwable $t
     * @param int $tabIndex
     * @return string
     */
    protected function exception2StrCompact(\Throwable $t, int $tabIndex = 0): string
    {
        $tabs = str_repeat("\t", $tabIndex);
        return $tabs . "{red}[{/}{yellow}" . get_class($t) . "{/}{red}][{yellow}#" . $t->getCode() . "{/}{red}] " .
            $t->getMessage();
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
        $errorLog = $this->app->kernel->errors;
        $errorsCount = $errorLog->count();
        if ($errorLog->count()) {
            $this->print("");
            $this->print($tabs . sprintf("{red}{b}%d{/}{red} errors caught!{/}", $errorsCount));
            /** @var \Charcoal\Apps\Kernel\Errors\ErrorMsg $errorMsg */
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
        } else {
            if ($displayNoErrorsCaught) {
                $this->print("{grey}No errors caught!{/}");
            }
        }
    }

    /**
     * @return void
     */
    public function printAppHeaders(): void
    {
        $this->print(sprintf("{yellow}{invert}Charcoal App Kernel{/} {grey}%s{/}", InstalledVersions::getPrettyVersion("charcoal-dev/app-kernel")), 200);
        $this->print(sprintf("{cyan}{invert}Charcoal CLI{/} {grey}%s{/}", InstalledVersions::getPrettyVersion("charcoal-dev/cli")), 200);

        // App Introduction
        $this->print("");
        $this->repeatChar("~", 5, 100, true);
        foreach (Banners::Digital(OOP::baseClassName($this->app::class))->lines as $line) {
            $this->print("{magenta}{invert}" . $line . "{/}");
        }

        $this->repeatChar("~", 5, 100, true);
        $this->print("");
    }
}
