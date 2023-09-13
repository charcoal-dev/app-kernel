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

namespace Charcoal\Apps\Kernel;

use Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface;
use Charcoal\Apps\Kernel\Errors\ErrorMsg;
use Charcoal\OOP\Traits\NoDumpTrait;
use Charcoal\OOP\Traits\NotCloneableTrait;
use Charcoal\OOP\Traits\NotSerializableTrait;

/**
 * Class Errors
 * @package Charcoal\Apps\Kernel
 */
class Errors implements \IteratorAggregate
{
    public readonly int $pathOffset;
    public int $debugBacktraceLevel = E_WARNING;
    public int $backtraceOffset = 3;
    public bool $exceptionHandlerShowTrace = true;

    private array $errorLog;
    private int $errorLogCount;

    use NoDumpTrait;
    use NotSerializableTrait;
    use NotCloneableTrait;

    /**
     * @param \Charcoal\Apps\Kernel\AppKernel $aK
     * @param \Charcoal\Apps\Kernel\Errors\ErrorLoggerInterface $logger
     */
    public function __construct(
        AppKernel                   $aK,
        public ErrorLoggerInterface $logger
    )
    {
        $this->pathOffset = strlen($aK->dir->root->path);
        $this->init();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        $this->errorLog = [];
        $this->errorLogCount = 0;
        set_error_handler([$this, "errorHandler"]);
        set_exception_handler([$this, "exceptionHandler"]);
    }

    /**
     * @return array
     */
    public function flushClean(): array
    {
        $errorLog = $this->errorLog;
        $this->flush();
        return $errorLog;
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->errorLog = [];
        $this->errorLogCount = 0;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        if ($this->errorLogCount > 0) {
            throw new \LogicException('App instance cannot be serialized with errors logged');
        }

        return [
            "pathOffset" => $this->pathOffset,
            "debugBacktraceLevel" => $this->debugBacktraceLevel,
            "backtraceOffset" => $this->backtraceOffset,
            "exceptionHandlerShowTrace" => $this->exceptionHandlerShowTrace
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->pathOffset = $data["pathOffset"];
        $this->debugBacktraceLevel = $data["debugBacktraceLevel"];
        $this->backtraceOffset = $data["backtraceOffset"];
        $this->exceptionHandlerShowTrace = $data["exceptionHandlerShowTrace"];
        $this->init();
    }

    /**
     * @param \Throwable $t
     * @return string
     */
    public function exceptionToString(\Throwable $t): string
    {
        return sprintf('[%s][#%s] %s', get_class($t), $t->getCode(), $t->getMessage());
    }

    /**
     * @param string|\Throwable $error
     * @param int $level
     * @param int $fileLineBacktraceIndex
     * @return void
     */
    public function trigger(string|\Throwable $error, int $level = E_USER_NOTICE, int $fileLineBacktraceIndex = 1): void
    {
        if ($error instanceof \Throwable) {
            $error = $this->exceptionToString($error);
        }

        if (!in_array($level, [E_USER_NOTICE, E_USER_WARNING])) {
            throw new \InvalidArgumentException('Invalid error level to trigger');
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->errorHandler(
            $level,
            $error,
            $trace[$fileLineBacktraceIndex]["file"] ?? "",
            intval($trace[$fileLineBacktraceIndex]["line"] ?? -1)
        );
    }

    /**
     * @param \Charcoal\Apps\Kernel\Errors\ErrorMsg $errorMsg
     * @return void
     */
    public function append(ErrorMsg $errorMsg): void
    {
        $this->errorLog[] = $errorMsg;
        $this->errorLogCount++;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->errorLogCount;
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->errorLog);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->errorLog;
    }

    /**
     * @param \Throwable $t
     * @return never
     */
    final public function exceptionHandler(\Throwable $t): never
    {
        $exception = [
            "class" => get_class($t),
            "message" => $t->getMessage(),
            "code" => $t->getCode(),
            "file" => $this->getOffsetFilepath($t->getFile()),
            "line" => $t->getLine(),
        ];

        if ($this->exceptionHandlerShowTrace) {
            $exception["trace"] = $t->getTrace();
        }

        header("Content-Type: application/json", response_code: 500);
        exit(json_encode(["FatalError" => $exception]));
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     */
    final public function errorHandler(int $level, string $message, string $file, int $line): bool
    {
        if (error_reporting() === 0) return false;

        $err = new ErrorMsg($this, $level, $message, $file, $line);
        $this->append($err);

        // Determine if execution should terminate
        if (!in_array($err->level, [2, 8, 512, 1024, 2048, 8192, 16384])) {
            header("Content-Type: application/json", response_code: 500);
            exit(json_encode(["FatalError" => [$err->levelStr, $err->message]]));
        }

        // Execution may continue
        return true;
    }

    /**
     * @param string $path
     * @return string
     */
    final public function getOffsetFilepath(string $path): string
    {
        return trim(substr($path, $this->pathOffset), DIRECTORY_SEPARATOR);
    }

    /**
     * @param int $level
     * @return string
     */
    final public function getErrorLevelStr(int $level): string
    {
        return match ($level) {
            1 => "Fatal Error",
            2, 512 => "Warning",
            4 => "Parse Error",
            8, 1024 => "Notice",
            16 => "Core Error",
            32 => "Core Warning",
            64 => "Compile Error",
            128 => "Compile Warning",
            256 => "Error",
            2048 => "Strict",
            4096 => "Recoverable",
            8192, 16384 => "Deprecated",
            default => "Unknown",
        };
    }
}
