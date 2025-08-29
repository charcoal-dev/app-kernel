<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\App\Kernel\Support\ErrorHelper;

/**
 * Defines an abstract error logger capable of formatting and logging errors with ANSI escape sequences.
 * Provides functionality to process exceptions, errors, and backtraces in a standardized format.
 */
abstract class AnsiErrorLogger
{
    /**
     * @param bool $useAnsiEscapeSeq
     * @param string $eolChar
     * @param int $pathOffset
     */
    public function __construct(
        public bool   $useAnsiEscapeSeq = true,
        public string $eolChar = PHP_EOL,
        public int    $pathOffset = 0
    )
    {
    }

    /**
     * Parses the provided exception and formats its details into an array of strings.
     */
    final protected function parseException(\Throwable $exception): array
    {
        $dto = ErrorHelper::getExceptionDto($exception);

        $buffer[] = "";
        $buffer[] = str_repeat(".", 10);
        $buffer[] = "";
        $buffer[] = sprintf("\e[36m[%s]\e[0m", date("d-m-Y H:i"));
        $buffer[] = sprintf("\e[33mCaught:\e[0m \e[31m%s\e[0m", $dto["class"]);
        $buffer[] = sprintf("\e[33mMessage:\e[0m %s", $dto["message"]);
        $buffer[] = sprintf("\e[33mCode:\e[0m %d", $dto["code"]);
        $buffer[] = sprintf("\e[33mFile:\e[0m \e[34m%s\e[0m", substr($dto["file"], $this->pathOffset));
        $buffer[] = sprintf("\e[33mLine:\e[0m \e[36m%d\e[0m", $dto["line"]);
        $this->parseTrace($buffer, $exception->getTrace());
        $buffer[] = "";
        $buffer[] = str_repeat(".", 10);
        $buffer[] = "";
        return $buffer;
    }

    /**
     * @param ErrorEntry $error
     */
    final protected function parseError(ErrorEntry $error): array
    {
        $buffer[] = "";
        $buffer[] = sprintf("\e[36m[%s]\e[0m", date("d-m-Y H:i"));
        $buffer[] = sprintf("\e[33mError:\e[0m \e[31m%s\e[0m", $error->level);
        $buffer[] = sprintf("\e[33mMessage:\e[0m %s", $error->message);
        $buffer[] = sprintf("\e[33mFile:\e[0m \e[34m%s\e[0m", $error->filepath);
        $buffer[] = sprintf("\e[33mLine:\e[0m \e[36m%d\e[0m", $error->line);
        if ($error->backtrace) {
            $this->parseTrace($buffer, $error->backtrace);
        }

        $buffer[] = "";
        return $buffer;
    }

    /**
     * Parses and appends trace information to the provided buffer.
     */
    private function parseTrace(array &$buffer, array $trace): void
    {
        if (!$trace) {
            return;
        }

        $buffer[] = "\e[33mBacktrace:\e[0m";
        $buffer[] = "┬";
        foreach ($trace as $sf) {
            $function = $sf["function"] ?? null;
            $class = $sf["class"] ?? null;
            $type = $sf["type"] ?? null;
            $file = $sf["file"] ?? null;
            $line = $sf["line"] ?? null;

            if ($file && is_string($file) && $line) {
                $method = $function;
                if ($class && $type) {
                    $method = $class . $type . $function;
                }

                $traceString = sprintf("\e[4m\e[36m%s\e[0m on line # \e[4m\e[33m%d\e[0m", $file, $line);
                if ($method) {
                    $traceString = sprintf("Method \e[4m\e[35m%s\e[0m in file ", $method) . $traceString;
                }

                $buffer[] = "├─ " . $traceString;
            }
        }
    }
}