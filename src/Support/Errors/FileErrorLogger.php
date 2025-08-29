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
use Charcoal\Filesystem\Path\FilePath;

/**
 * Logs errors and exceptions to a specified file while providing options for
 * formatting using ANSI escape sequences and custom end-of-line characters.
 * @api
 */
final class FileErrorLogger implements ErrorLoggerInterface
{
    public readonly string $logFile;
    public bool $isWriting = true;

    public function __construct(
        FilePath|string $logFile,
        public bool     $useAnsiEscapeSeq = true,
        public string   $eolChar = PHP_EOL,
        public int      $pathOffset = 0
    )
    {
        if ($logFile instanceof FilePath) {
            if (!$logFile->writable) {
                throw new \RuntimeException("Error log file is not writable");
            }

            $this->logFile = $logFile->absolute;
            return;
        }

        $logFile = realpath($logFile);
        if (!file_exists($logFile) || !is_file($logFile) || !is_writable($logFile)) {
            throw new \RuntimeException("Error log file does not exist");
        }

        $this->logFile = $logFile;
    }

    /**
     * @internal
     */
    private function getLines(\Throwable|ErrorEntry $error): array
    {
        if (!$this->isWriting) {
            return [];
        }

        return match (true) {
            $error instanceof ErrorEntry => AnsiErrorParser::parseError($error),
            default => AnsiErrorParser::parseException($error),
        };
    }

    /**
     * @internal
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
     * @internal
     */
    public function handleError(ErrorEntry $error): void
    {
        $this->writeToFile($this->getLines($error));
    }

    /**
     * @internal
     */
    public function handleException(\Throwable $exception): void
    {
        $this->writeToFile($this->getLines($exception));
    }
}