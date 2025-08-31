<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Errors\AnsiErrorDecorator;
use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * A decorator class for handling and logging errors or exceptions to the console.
 * This class supports optional ANSI escape sequences for styling error output
 * and allows customization of the end-of-line character used in output.
 * @api
 */
final class ConsoleErrorWriter extends AnsiErrorDecorator
{
    public function __construct(
        bool    $useAnsiEscapeSeq = true,
        string  $eolChar = "\n",
        string  $tabChar = "\t",
        ?string $template = null,
    )
    {
        parent::__construct($useAnsiEscapeSeq, $eolChar, $tabChar, $template);
    }

    /**
     * @param array<array<string>> $buffer
     * @return void
     */
    private function write(array $buffer): void
    {
        echo $this->eolChar;
        foreach ($buffer as $decorateDto) {
            foreach ($decorateDto as $line) {
                echo $line . $this->eolChar;
            }
        }

        echo $this->eolChar;
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