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

namespace Charcoal\Apps\Kernel\Errors;

use Charcoal\Apps\Kernel\Exception\AppKernelException;
use Charcoal\Filesystem\File;

/**
 * Class FileErrorLogger
 * @package Charcoal\Apps\Kernel\Errors
 */
class FileErrorLogger implements ErrorLoggerInterface
{
    public readonly string $logFilePath;
    public bool $isWriting = true;

    /**
     * @param \Charcoal\Filesystem\File $logFile
     * @param bool $useAnsiEscapeSeq
     * @param string $eolChar
     * @throws \Charcoal\Apps\Kernel\Exception\AppKernelException
     * @throws \Charcoal\Filesystem\Exception\FilesystemException
     */
    public function __construct(
        File          $logFile,
        public bool   $useAnsiEscapeSeq = true,
        public string $eolChar = PHP_EOL
    )
    {
        if (!$logFile->isWritable()) {
            throw new AppKernelException('Error log file is not writable');
        }

        $this->logFilePath = $logFile->path;
    }

    /**
     * @param \Charcoal\Apps\Kernel\Errors\ErrorMsg|\Throwable $error
     * @return void
     */
    public function write(ErrorMsg|\Throwable $error): void
    {
        if (!$this->isWriting) {
            return;
        }

        $error instanceof ErrorMsg ?
            $this->writeError($error) : $this->writeException($error);
    }

    /**
     * @param \Throwable $t
     * @return void
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
     * @param \Charcoal\Apps\Kernel\Errors\ErrorMsg $error
     * @return void
     */
    private function writeError(ErrorMsg $error): void
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
     * @param array $buffer
     * @return void
     */
    private function writeToFile(array $buffer): void
    {
        $buffer = implode($this->eolChar, $buffer);
        if (!$this->useAnsiEscapeSeq) {
            $buffer = preg_replace("/\\e\[\d+m/", "", $buffer);
        }

        if (!file_put_contents($this->logFilePath, implode($this->eolChar, $buffer))) {
            throw new \RuntimeException('Failed to write to error log file');
        }
    }

    /**
     * @param array $buffer
     * @param array $trace
     * @return void
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
