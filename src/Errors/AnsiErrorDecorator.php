<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\App\Kernel\Support\PathHelper;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Console\Ansi\AnsiDecorator;

/**
 * This class uses templates to format error information, supports customizable
 * end-of-line and tab characters, and optionally leverages ANSI escape sequences
 * for styling.
 *
 * Implements the ErrorLoggerInterface to ensure compatibility with error logging systems.
 */
abstract class AnsiErrorDecorator implements ErrorLoggerInterface
{
    private string $template;
    private string $dtHeader;
    private string $traceLineTpl;
    private string $previousBoundary;
    private array $templateVars;
    private array $templatePrep;

    public function __construct(
        public bool   $useAnsiEscapeSeq = true,
        public string $eolChar = PHP_EOL,
        public string $tabChar = "\t",
        ?array        $template = null,
    )
    {
        $template = $template ?: self::defaultTemplate();
        $this->dtHeader = array_shift($template);
        $this->traceLineTpl = array_shift($template);
        $this->previousBoundary = array_shift($template);
        $this->template = implode($this->eolChar, $template);

        $templateVars = ["class", "message", "code", "file", "line"];
        $this->templatePrep = array_map(fn($k) => "@{:" . $k . ":}", $templateVars);
        $this->templateVars = array_fill_keys($templateVars, "1");
    }

    /**
     * @param \Throwable|ErrorEntry $error
     * @return array<array<string>>
     */
    final protected function getLines(\Throwable|ErrorEntry $error): array
    {
        $errors = $error instanceof \Throwable ?
            ErrorHelper::getExceptionChain($error, reverse: true) : [$error];

        $result = [];
        $tabIndex = -1;
        foreach ($errors as $error) {
            $tabIndex++;
            $tabs = str_repeat($this->tabChar, min(3, $tabIndex));
            $dto = ErrorHelper::getErrorDto($error, trace: true);
            $trace = $dto["trace"] ?? null;
            $dto = array_intersect_key($dto, $this->templateVars);
            $dto["file"] = PathHelper::takeLastParts($dto["file"], 2);
            $templated = strtr($this->template, array_combine($this->templatePrep, array_values($dto)));
            $lines = array_map(fn($l) => $tabs . $l, preg_split("/\r\n|\r|\n/", $templated));
            if ($tabIndex === 0) {
                array_unshift($lines, $tabs . sprintf($this->dtHeader, date("Y-m-d H:i:s")));
            }

            if ($trace) {
                foreach ($trace as $tL) {
                    $lines[] = $tabs . sprintf(
                            $this->traceLineTpl,
                            PathHelper::takeLastParts($tL["file"], 2),
                            $tL["line"] ?? -1);
                }
            }

            if ($error instanceof \Throwable) {
                $nextInChain = $errors[$tabIndex + 1] ?? null;
                if ($nextInChain) {
                    $lines[] = $tabs . sprintf($this->previousBoundary, ObjectHelper::baseClassName($nextInChain));
                }
            }

            $result[] = preg_split("/\r\n|\r|\n/", AnsiDecorator::parse(implode($this->eolChar, $lines)));
        }

        return $result;
    }

    /**
     * @return string[]
     */
    protected static function defaultTemplate(): array
    {
        return [
            // [0]: Datetime Header
            "{magenta}[%1\$s]{/}",
            // [1]: Backtrace line template:
            "\x20\x20\x20\x20\x20\x20\x20{cyan}[%1\$s {yellow}#%2\$s{cyan}]{/}",
            // [2]: Next boundary
            "{yellow}Caught By ⤵{/} {grey}%s{/}",
            // [...]: Error DTO template:
            "{red}[@{:class:}][{yellow}#@{:code:}{red}]{/}",
            "@{:message:}",
            "{yellow}Trace:{/}\x20{blue}[@{:file:} {yellow}\#@{:line:}{blue}]{/}"];
    }
}