<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\App\Kernel\Support\PathHelper;
use Charcoal\Console\Ansi\AnsiDecorator;

/**
 * A decorator class for handling and logging errors or exceptions to the console.
 * This class supports optional ANSI escape sequences for styling error output
 * and allows customization of the end-of-line character used in output.
 * @api
 */
final readonly class ConsoleErrorWriter implements ErrorLoggerInterface
{
    private string $template;
    private array $templateVars;

    public function __construct(
        public bool   $useAnsiEscapeSeq = true,
        public string $eolChar = "\n",
        public string $tabChar = "\t",
        ?string       $template = null,
    )
    {
        $this->template = $template ?: ErrorHelper::errorDtoTemplate();
        $this->templateVars = ["datetime", "class", "code", "message",
            "file", "file2", "line", "trace", "next"];
    }

    /**
     * @param \Throwable|ErrorEntry $error
     * @return array<array<string>>
     */
    private function getLines(\Throwable|ErrorEntry $error): array
    {
        $errors = $error instanceof \Throwable ?
            ErrorHelper::getExceptionChain($error, reverse: true) : [$error];

        $result = [];
        $tabIndex = -1;
        foreach ($errors as $error) {
            $tabIndex++;
            $tabs = str_repeat($this->tabChar, $tabIndex);
            $lines = explode($this->eolChar, $this->getTemplated($error));
            foreach ($lines as $line) {
                $result[] = $tabs . $line;
            }
        }

        return $result;
    }

    /**
     * @param \Throwable|ErrorEntry $error
     * @return string
     */
    private function getTemplated(\Throwable|ErrorEntry $error): string
    {
        $dto = ErrorHelper::getErrorDto($error, trace: true);
        $dto["datetime"] = date("d-m-Y H:i:s");
        $dto["file2"] = PathHelper::takeLastParts($dto["file"], 2);
        if (isset($dto["trace"])) {
            $trace = [];
            foreach ($dto["trace"] as $et) {
                if (isset($et["file"])) {
                    $trace[] = PathHelper::takeLastParts($et["file"]) . "@" . ($et["line"] ?? -1);
                }
            }

            $dto["trace"] = implode(", ", $trace);
        }

        if (!isset($dto["trace"])) {
            $dto["trace"] = "~";
        }

        $dto["next"] = $dto["previous"]["class"] ?? "~";
        $data = array_intersect_key($dto, array_fill_keys($this->templateVars, "~"));
        $templated = strtr($this->template, array_combine(array_map(fn($k) => "{{" . $k . "}}", $this->templateVars),
            array_values($data)));

        return AnsiDecorator::parse($templated, true, strip: $this->useAnsiEscapeSeq);
    }

    /**
     * @param array<array<string>> $buffer
     * @return void
     */
    private function write(array $buffer): void
    {
        foreach ($buffer as $decorateDto) {
            foreach ($decorateDto as $line) {
                echo $line . $this->eolChar;
            }

            echo $this->eolChar;
        }
    }

    /**
     * Handles the provided error by processing and printing its details.
     */
    public function handleError(ErrorEntry $error): void
    {
        $this->write($this->getLines($error));
    }

    /**
     * Handles the provided exception by processing and printing its details.
     */
    public function handleException(\Throwable $exception): void
    {
        $this->write($this->getLines($exception));
    }
}