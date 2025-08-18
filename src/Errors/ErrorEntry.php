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
final readonly class ErrorEntry
{
    public string $level;
    public string $filepath;
    public ?array $backtrace;

    public function __construct(
        ErrorManager  $errorService,
        public int    $errno,
        public string $message,
        string        $file,
        public int    $line,
    )
    {
        $this->filepath = $errorService->getRelativeFilepath($file);
        $this->level = self::getErrorLevelStr($this->errno);
        $this->backtrace = $errorService->debugging ? array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            $errorService->debugBacktraceOffset(null)) : null;
    }

    /**
     * @param int $level
     * @return string
     */
    public static function getErrorLevelStr(int $level): string
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