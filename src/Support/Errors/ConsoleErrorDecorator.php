<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Errors\ErrorEntry;

/**
 * A decorator class for handling and logging errors or exceptions to the console.
 * This class supports optional ANSI escape sequences for styling error output
 * and allows customization of the end-of-line character used in output.
 * @api
 */
final class ConsoleErrorDecorator implements ErrorLoggerInterface
{
    public function __construct(
        public bool   $useAnsiEscapeSeq = true,
        public string $eolChar = "\n",
    )
    {
    }

    private function getLines(\Throwable|ErrorEntry $error): array
    {
        return match (true) {
            $error instanceof ErrorEntry => AnsiErrorParser::parseError($error),
            default => AnsiErrorParser::parseException($error),
        };
    }

    /**
     * @internal
     */
    private function write(array $buffer): void
    {
        if (!$this->useAnsiEscapeSeq) {
            $buffer = AnsiErrorParser::strip($buffer);
        }

        echo implode($this->eolChar, $buffer);
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