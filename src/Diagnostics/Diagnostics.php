<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

use Charcoal\App\Kernel\Clock\MonotonicTimestamp;
use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Diagnostics\Events\DiagnosticsEvents;
use Charcoal\App\Kernel\Diagnostics\Events\DiagnosticsEventsContext;
use Charcoal\App\Kernel\Diagnostics\Events\ExceptionCaughtBroadcast;
use Charcoal\App\Kernel\Diagnostics\Events\LogEntryBroadcast;
use Charcoal\App\Kernel\Enums\LogLevel;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\Base\Traits\InstanceOnStaticScopeTrait;
use Charcoal\Base\Traits\NotCloneableTrait;

/**
 * Class Diagnostics
 * @package Charcoal\App\Kernel\Diagnostics
 */
final class Diagnostics implements ErrorLoggerInterface
{
    use InstanceOnStaticScopeTrait;
    use NotCloneableTrait;

    private static ?self $instance = null;

    private DiagnosticsEvents $logEntryEvent;
    public readonly int $startupTime;
    public readonly int $bootstrapTime;

    private array $logs = [];
    private array $metrics = [];

    /**
     * @internal
     */
    protected function __construct(MonotonicTimestamp $startTime)
    {
        $this->logEntryEvent = new DiagnosticsEvents("app.diagnostics.logEntry", [
            DiagnosticsEventsContext::class,
            LogEntryBroadcast::class,
            ExceptionCaughtBroadcast::class,
        ]);

        $this->startupTime = $startTime->elapsedTo(MonotonicTimestamp::now());
    }

    /**
     * @return self
     */
    public static function app(): self
    {
        return self::getInstance();
    }

    /**
     * @param bool $log
     * @return ExecutionMetrics
     */
    public function metricsSnapshot(bool $log = false): ExecutionMetrics
    {
        $metrics = new ExecutionMetrics();
        if ($log) {
            $this->metrics[] = $metrics;
        }

        return $metrics;
    }

    /**
     * @param bool $metrics
     * @param bool $clean
     * @return ExecutionSnapshot
     */
    public function snapshot(bool $metrics = true, bool $clean = true): ExecutionSnapshot
    {
        if ($metrics) {
            $this->metricsSnapshot(true);
        }

        $snapshot = new ExecutionSnapshot($this->startupTime, $this->metrics, $this->logs);
        if ($clean) {
            $this->logs = [];
            $this->metrics = [];
        }

        return $snapshot;
    }

    /**
     * @param LogLevel $level
     * @param string $message
     * @param array $context
     * @param \Exception|null $exception
     * @return void
     */
    private function logEntry(
        LogLevel    $level,
        string      $message,
        #[\SensitiveParameter]
        array       $context = [],
        ?\Throwable $exception = null
    ): void
    {
        $log = new LogEntry($level, $message, $context, $exception);
        $this->logs[] = $log;
        $this->logEntryEvent->dispatch($log);
    }

    /**
     * Logs a debugging message with the specified context.
     */
    public function debug(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $this->logEntry(LogLevel::Debug, $message, $context, $exception);
    }

    /**
     * Logs an informational message with the specified context.
     */
    public function info(string $message, array $context = []): void
    {
        $this->logEntry(LogLevel::Info, $message, $context);
    }

    /**
     * Logs a warning message with the specified context and optional exception.
     */
    public function warning(
        string                       $message,
        #[\SensitiveParameter] array $context = [],
        ?\Throwable                  $exception = null
    ): void
    {
        $this->logEntry(LogLevel::Warning, $message, $context, $exception);
    }

    /**
     * Logs an error message with the specified context and optional exception.
     */
    public function error(
        string                       $message,
        #[\SensitiveParameter] array $context = [],
        ?\Throwable                  $exception = null
    ): void
    {
        $this->logEntry(LogLevel::Error, $message, $context, $exception);
    }

    /**
     * Logs a critical message with the specified context and optional exception.
     */
    public function critical(
        string                       $message,
        #[\SensitiveParameter] array $context = [],
        ?\Exception                  $exception = null
    ): void
    {
        $this->logEntry(LogLevel::Critical, $message, $context, $exception);
    }

    /**
     * @param ErrorEntry $errorEntry
     * @return void
     * @internal
     */
    private function logFromError(ErrorEntry $errorEntry): void
    {
        $local = match ($errorEntry->errno) {
            E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED => LogLevel::Notice,
            E_WARNING, E_USER_WARNING => LogLevel::Warning,
            E_ERROR, E_USER_ERROR => LogLevel::Error,
            default => LogLevel::Debug,
        };

        $this->logEntry($local, $errorEntry->message, [$errorEntry]);
    }

    /**
     * @param ErrorEntry $error
     * @return void
     * @internal
     */
    public function handleError(ErrorEntry $error): void
    {
        $this->logFromError($error);
    }

    public function handleException(\Throwable $exception): void
    {
    }
}