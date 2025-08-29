<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Diagnostics;

use Charcoal\App\Kernel\Clock\Clock;
use Charcoal\App\Kernel\Clock\MonotonicTimestamp;
use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Diagnostics\Events\BuildStageEvents;
use Charcoal\App\Kernel\Diagnostics\Events\DiagnosticsEvents;
use Charcoal\App\Kernel\Diagnostics\Events\DiagnosticsEventsContext;
use Charcoal\App\Kernel\Diagnostics\Events\ExceptionCaughtBroadcast;
use Charcoal\App\Kernel\Enums\DiagnosticsEvent;
use Charcoal\App\Kernel\Enums\LogLevel;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Base\Traits\InstanceOnStaticScopeTrait;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Base\Traits\NotSerializableTrait;
use Charcoal\Events\Stats\EventStats;

/**
 * Class Diagnostics
 * @package Charcoal\App\Kernel\Diagnostics
 */
final class Diagnostics implements ErrorLoggerInterface
{
    use InstanceOnStaticScopeTrait;
    use NotCloneableTrait;
    use NotSerializableTrait;
    use NoDumpTrait;

    private static ?self $instance = null;

    private DiagnosticsEvents $events;
    private array $eventListeners = [];
    private array $logs = [];
    private array $metrics = [];
    private ?Clock $clock = null;
    public readonly int $startupTime;

    /**
     * @internal
     */
    protected function __construct()
    {
        $this->events = new DiagnosticsEvents("app.Events.Diagnostics", [
            DiagnosticsEventsContext::class,
            LogEntry::class,
            ExceptionCaughtBroadcast::class,
            BuildStageEvents::class
        ]);
    }

    /**
     * @param DiagnosticsEvent $event
     * @param \Closure $callback
     * @return void
     */
    public function subscribe(DiagnosticsEvent $event, \Closure $callback): void
    {
        if (isset($this->startupTime) && $event === DiagnosticsEvent::BuildStage) {
            throw new \DomainException("Cannot subscribe to BuildState before startup");
        }

        $subscription = $this->events->subscribe();
        $this->eventListeners[$event->name][] = $subscription;
        $subscription->listen($event->getEventContext(), $callback);
    }

    /**
     * @param bool $basename
     * @return EventStats
     * @api
     */
    public function eventInspection(bool $basename = false): EventStats
    {
        return $this->events->inspect($basename);
    }

    /**
     * @param Clock $clock
     * @param MonotonicTimestamp $startTime
     * @return void
     * @internal
     */
    public function setStartupTime(Clock $clock, MonotonicTimestamp $startTime): void
    {
        $this->clock = $clock;
        $this->startupTime = $startTime->elapsedTo(MonotonicTimestamp::now());

        // Clean up BuildStage listeners
        $subscribers = $this->eventListeners[DiagnosticsEvent::BuildStage->name] ?? null;
        if ($subscribers) {
            foreach ($subscribers as $subscriber) {
                $subscriber->unsubscribe();
            }

            unset($this->eventListeners[DiagnosticsEvent::BuildStage->name]);
        }
    }

    /**
     * @return self
     */
    public static function app(): self
    {
        return self::getInstance();
    }

    /**
     * Resets the static instance to null.
     * @internal
     */
    final public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Take a runtime metrics snapshot for runtime execution debugging/inspection.
     * Provides memory/CPU usage metrics.
     * @api
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
     * Take an immutable snapshot of the diagnostics for runtime execution debugging/inspection.
     * @api
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
     * @internal
     */
    private function logEntry(
        LogLevel    $level,
        string      $message,
        #[\SensitiveParameter]
        array       $context = [],
        ?\Throwable $exception = null
    ): void
    {
        $log = new LogEntry($level, $message, $context, $exception,
            $this->clock?->getImmutable() ?? new \DateTimeImmutable());
        $this->logs[] = $log;
        $this->events->dispatch($log);
    }

    /**
     * Logs a verbose/internal/arbitrary message with the specified context.
     * @api
     */
    public function verbose(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $this->logEntry(LogLevel::Verbose, $message, $context, $exception);
    }

    /**
     * Logs a debugging message with the specified context.
     * @api
     */
    public function debug(string $message, array $context = [], ?\Throwable $exception = null): void
    {
        $this->logEntry(LogLevel::Debug, $message, $context, $exception);
    }

    /**
     * Logs an informational message with the specified context.
     * @api
     */
    public function info(string $message, array $context = []): void
    {
        $this->logEntry(LogLevel::Info, $message, $context);
    }

    /**
     * Logs a notice message with the specified context.
     * @api
     */
    public function notice(string $message, array $context = []): void
    {
        $this->logEntry(LogLevel::Notice, $message, $context);
    }

    /**
     * Logs a warning message with the specified context and optional exception.
     * @api
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
     * @api
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
     * @api
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
     * @internal
     */
    public function handleError(ErrorEntry $error): void
    {
        $this->logFromError($error);
    }

    /**
     * @internal
     */
    public function handleException(\Throwable $exception): void
    {
        $this->error(sprintf('Caught "%s" exception', ObjectHelper::baseClassName(get_class($exception))),
            exception: $exception);
    }

    /**
     * @param BuildStageEvents $state
     * @return void
     * @internal
     */
    public function buildStageStream(BuildStageEvents $state): void
    {
        if (isset($this->startupTime)) {
            throw new \DomainException("BuildStageEvents stream is now closed");
        }

        $this->events->dispatch($state);
    }
}