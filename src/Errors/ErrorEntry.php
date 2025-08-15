<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

/**
 * Class ErrorEntry
 * @package Charcoal\App\Kernel\Errors
 */
readonly class ErrorEntry
{
    public string $filePath;
    public string $levelStr;
    public ?array $backtrace;

    public function __construct(
        ErrorHandler $errors,
        public int    $level,
        public string $message,
        string        $file,
        public int    $line,
    )
    {
        $this->filePath = $errors->getOffsetFilepath($file);
        $this->levelStr = $errors->getErrorLevelStr($this->level);
        $this->backtrace = ($errors->debugBacktraceLevel <= $this->level) ?
            array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), $errors->backtraceOffset) : null;
    }
}