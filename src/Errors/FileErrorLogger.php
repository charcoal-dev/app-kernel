<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Contracts\Error\ErrorLoggerInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\Filesystem\Path\FilePath;

/**
 * Class FileErrorLogger
 * @package Charcoal\App\Kernel\Errors
 */
final class FileErrorLogger implements ErrorLoggerInterface
{
    public readonly string $logFile;
    public bool $isWriting = true;

    public function __construct(
        FilePath      $logFile,
        public bool   $useAnsiEscapeSeq = true,
        public string $eolChar = PHP_EOL
    )
    {
        if (!$logFile->writable) {
            throw new \RuntimeException("Error log file is not writable");
        }

        $this->logFile = $logFile->absolute;
    }

    /**
     * Main-exposed method
     */
    public function write(\Throwable|ErrorEntry $error): void
    {
        if (!$this->isWriting) {
            return;
        }

        $error instanceof ErrorEntry ?
            $this->writeError($error) : $this->writeException($error);
    }

    /**
     * Prepares & formats a \Throwable object and writes into the log file
     */
    private function writeException(\Throwable $t): void
    {
        $buffer[] = "";
        $buffer[] = str_repeat(".", 10);
        $buffer[] = "";
        $buffer[] = sprintf("\e[36m[%s]\e[0m", date("d-m-Y H:i"));
        $buffer[] = sprintf("\e[33mCaught:\e[0m \e[31m%s\e[0m", get_class($t));
        $buffer[] = sprintf("\e[33mMessage:\e[0m %s", $t->getMessage());
        $buffer[] = sprintf("\e[33mFile:\e[0m \e[34m%s\e[0m", $t->getFile());
        $buffer[] = sprintf("\e[33mLine:\e[0m \e[36m%d\e[0m", $t->getLine());
        $this->bufferTrace($buffer, $t->getTrace());
        $buffer[] = "";
        $buffer[] = str_repeat(".", 10);
        $buffer[] = "";
        $this->writeToFile($buffer);
    }

    /**
     * Prepares & formats an ErrorEntry object and writes into the log file
     */
    private function writeError(ErrorEntry $error): void
    {
        $buffer[] = "";
        $buffer[] = sprintf("\e[36m[%s]\e[0m", date("d-m-Y H:i"));
        $buffer[] = sprintf("\e[33mError:\e[0m \e[31m%s\e[0m", $error->levelStr);
        $buffer[] = sprintf("\e[33mMessage:\e[0m %s", $error->message);
        $buffer[] = sprintf("\e[33mFile:\e[0m \e[34m%s\e[0m", $error->filePath);
        $buffer[] = sprintf("\e[33mLine:\e[0m \e[36m%d\e[0m", $error->line);
        if ($error->backtrace) {
            $this->bufferTrace($buffer, $error->backtrace);
        }

        $buffer[] = "";
        $this->writeToFile($buffer);
    }

    /**
     * Writes buffer data to log file
     */
    private function writeToFile(array $buffer): void
    {
        $buffer = implode($this->eolChar, $buffer);
        if (!$this->useAnsiEscapeSeq) {
            $buffer = preg_replace("/\\e\[\d+m/", "", $buffer);
        }

        error_clear_last();
        if (!@file_put_contents($this->logFile, $buffer)) {
            throw new \RuntimeException('Failed to write to error log file',
                previous: ErrorHelper::lastErrorToRuntimeException());
        }
    }

    /**
     * Prepares & formats debug backtrace and appends to buffer
     */
    private function bufferTrace(array &$buffer, array $trace): void
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