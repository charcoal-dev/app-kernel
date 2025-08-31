<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Errors\AnsiErrorDecorator;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\App\Kernel\Support\ErrorHelper;
use Charcoal\Filesystem\Path\FilePath;
use Charcoal\Filesystem\Path\SafePath;

/**
 * Logs errors and exceptions to a specified file while providing options for
 * formatting using ANSI escape sequences and custom end-of-line characters.
 * @api
 */
final class FileErrorLogger extends AnsiErrorDecorator
{
    public readonly string $logFile;
    private bool $isWriting = false;

    public function __construct(
        FilePath|SafePath|string $logFile,
        bool                     $useAnsiEscapeSeq = true,
        string                   $eolChar = PHP_EOL,
        string                   $tabChar = "\t",
        ?string                  $template = null,
    )
    {
        parent::__construct($useAnsiEscapeSeq, $eolChar, $tabChar, $template);

        if ($logFile instanceof FilePath) {
            if (!$logFile->writable) {
                throw new \RuntimeException("Error log file is not writable");
            }

            $this->logFile = $logFile->absolute;
            return;
        }

        if ($logFile instanceof SafePath) {
            $logFile = $logFile->path;
        }

        $logFile = realpath($logFile);
        if (!$logFile) {
            throw new \RuntimeException("Error log file path is invalid");
        }

        if (!file_exists($logFile) || !is_file($logFile) || !is_writable($logFile)) {
            throw new \RuntimeException("Error log file does not exist");
        }

        $this->logFile = $logFile;
    }

    /**
     * @param array $dtoObjects
     * @return void
     */
    private function writeToFile(array $dtoObjects): void
    {
        if ($this->isWriting) {
            return;
        }

        $this->isWriting = true;

        error_clear_last();
        $fp = @fopen($this->logFile, "a");
        if (!$fp) {
            throw new \RuntimeException("Failed to open error log file for writing",
                previous: ErrorHelper::lastErrorToRuntimeException());
        }

        foreach ($dtoObjects as $dtoObject) {
            if (!@fwrite($fp, implode($this->eolChar, $dtoObject) . $this->eolChar)) {
                throw new \RuntimeException("Failed to write to error log file",
                    previous: ErrorHelper::lastErrorToRuntimeException());
            }
        }

        fclose($fp);
        $this->isWriting = false;
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