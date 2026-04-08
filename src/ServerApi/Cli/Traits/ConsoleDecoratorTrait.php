<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli\Traits;

use Charcoal\Base\Objects\ObjectHelper;
use Charcoal\Cli\Display\Banners;
use Composer\InstalledVersions;

/**
 * Trait ConsoleDecoratorTrait
 * @package Charcoal\App\Kernel\ServerApi\Cli\Traits
 */
trait ConsoleDecoratorTrait
{
    /**
     * @return void
     */
    private function printAppHeaders(): void
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
    private function printAppClassBanner(): void
    {
        $this->repeatChar("~", 5, 100, true);
        foreach (Banners::Digital(ObjectHelper::baseClassName($this->app::class))->lines as $line) {
            $this->print("{magenta}{invert}" . $line . "{/}");
        }

        $this->repeatChar("~", 5, 100, true);
        $this->print("");
    }

    /**
     * @param int $tabIndex
     * @param bool $displayCompact
     * @param bool $displayNoErrorsCaught
     * @return void
     */
    private function printErrors(
        int  $tabIndex = 0,
        bool $displayCompact = true,
        bool $displayNoErrorsCaught = false
    ): void
    {
        $tabs = str_repeat("\t", $tabIndex);
        $errorsCount = $this->errorLog->count();
        if (!$errorsCount) {
            if ($displayNoErrorsCaught) {
                $this->print("{grey}No errors caught!{/}");
            }

            return;
        }

        $this->print("");
        $this->print($tabs . sprintf("{red}{b}%d{/}{red} errors caught!{/}", $errorsCount));
        foreach ($this->errorLog->errors as $errorMsg) {
            if ($displayCompact) {
                $this->print($tabs . sprintf('[{red}{b}%s{/}]{red} %s{/}', $errorMsg->level, $errorMsg->message));
                $this->print($tabs . sprintf('⮤ in {magenta}%s{/} on line {magenta}%d{/}', $errorMsg->filepath, $errorMsg->line));
            } else {
                $this->print($tabs . sprintf('{grey}│  ┌ {/}{yellow}Type:{/} {magenta}%s{/}', strtoupper($errorMsg->level)));
                $this->print($tabs . sprintf('{grey}├──┼ {/}{yellow}Message:{/} %s', $errorMsg->message));
                $this->print($tabs . sprintf("{grey}│  ├ {/}{yellow}File:{/} {cyan}%s{/}", $errorMsg->level));
                $this->print($tabs . sprintf("{grey}│  └ {/}{yellow}Line:{/} %d", $errorMsg->line));
                $this->print($tabs . "{grey}│{/}");
            }
        }

        $this->print("");
    }
}