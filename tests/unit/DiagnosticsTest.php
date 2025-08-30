<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Unit;

use Charcoal\App\Kernel\Clock\Clock;
use Charcoal\App\Kernel\Clock\MonotonicTimestamp;
use Charcoal\App\Kernel\Diagnostics\Diagnostics;
use Charcoal\App\Kernel\Diagnostics\ExecutionMetrics;
use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\App\Kernel\Errors\ErrorManager;
use Charcoal\App\Kernel\Internal\PathRegistry;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\PathInfo;
use Charcoal\Tests\App\Fixtures\Enums\TimezoneEnum;
use PHPUnit\Framework\TestCase;

/**
 * Class DiagnosticsTest
 * @package Charcoal\Tests\App\Unit
 */
class DiagnosticsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Clock::initialize(TimezoneEnum::UTC);
    }

    protected function setUp(): void
    {
        // Fresh Diagnostics instance and startup timestamp for each test
        Diagnostics::initialize()->setStartupTime(Clock::getInstance(),MonotonicTimestamp::now());
    }

    protected function tearDown(): void
    {
        // Clean internal state (logs/metrics) after each test
        Diagnostics::reset();
    }

    public function testMetricsSnapshotDoesNotLogByDefault(): void
    {
        $diag = Diagnostics::app();

        // Create a metrics object but do not store it
        $diag->metricsSnapshot();

        // Do not auto-append metrics on snapshot
        $snapshot = $diag->snapshot(metrics: false);

        $this->assertIsArray($snapshot->metrics);
        $this->assertCount(0, $snapshot->metrics, 'metricsSnapshot() without log=true should not be stored');
    }

    public function testMetricsSnapshotAppendsWhenLogTrue(): void
    {
        $diag = Diagnostics::app();

        // Store metrics in diagnostics
        $diag->metricsSnapshot(true);

        // Do not append an extra metrics item during snapshot
        $snapshot = $diag->snapshot(metrics: false);

        $this->assertCount(1, $snapshot->metrics);
        $this->assertInstanceOf(ExecutionMetrics::class, $snapshot->metrics[0]);
    }

    public function testSnapshotAppendsMetricsByDefaultAndCleansState(): void
    {
        $diag = Diagnostics::app();

        // Default snapshot: appends a metrics entry and cleans the internal state
        $first = $diag->snapshot();
        $this->assertCount(1, $first->metrics, 'snapshot() should append one metrics entry by default');

        // Next snapshot without adding metrics should see a clean slate
        $second = $diag->snapshot(metrics: false);
        $this->assertCount(0, $second->metrics, 'previous clean should reset stored metrics');
    }

    public function testSnapshotWithCleanFalseKeepsLogsAndMetrics(): void
    {
        $diag = Diagnostics::app();

        // Seed with one log and one metrics record
        $diag->debug('initial');
        $diag->metricsSnapshot(true);

        $snap1 = $diag->snapshot(metrics: false, clean: false);
        $this->assertCount(1, $snap1->logs);
        $this->assertCount(1, $snap1->metrics);

        // Add one more log; metrics unchanged
        $diag->info('second');
        $snap2 = $diag->snapshot(metrics: false, clean: false);
        $this->assertCount(2, $snap2->logs, 'logs should accumulate when clean=false');
        $this->assertCount(1, $snap2->metrics, 'metrics count should remain the same without appending');

        // Append another metrics snapshot and verify accumulation
        $diag->metricsSnapshot(true);
        $snap3 = $diag->snapshot(metrics: false, clean: false);
        $this->assertCount(2, $snap3->metrics, 'metrics should accumulate when clean=false and log=true');
    }

    public function testLoggingMethodsProduceEntriesAndCleanupWorks(): void
    {
        $diag = Diagnostics::app();

        $diag->verbose('v');
        $diag->debug('d');
        $diag->info('i');
        $diag->notice('n');
        $diag->warning('w');
        $diag->error('e');
        $diag->critical('c');

        $first = $diag->snapshot(metrics: false);
        $this->assertCount(7, $first->logs, 'all logging methods should produce a log entry');

        // After snapshot(), state is cleaned by default; add one more to verify cleanup
        $diag->debug('after-clean');
        $second = $diag->snapshot(metrics: false);
        $this->assertCount(1, $second->logs, 'logs should be cleaned after snapshot() and only contain new entries');
    }

    public function testHandleErrorAddsLogEntry(): void
    {
        $diag = Diagnostics::app();

        $entry = new ErrorEntry(
            E_USER_WARNING,
            'User warning occurred',
            __FILE__,
            __LINE__
        );

        $diag->handleError($entry);

        $snapshot = $diag->snapshot(metrics: false);
        $this->assertCount(1, $snapshot->logs, 'handleError() should produce a log entry');
    }

    public function testHandleExceptionProducesErrorLogEntry(): void
    {
        $diag = Diagnostics::app();

        $diag->handleException(new \RuntimeException('boom'));

        $snapshot = $diag->snapshot(metrics: false);
        $this->assertCount(1, $snapshot->logs, 'handleException() should produce a log entry');
    }

    public function testExecutionSnapshotConvertsStartupTimeToSeconds(): void
    {
        $diag = Diagnostics::app();

        $snapshot = $diag->snapshot(metrics: false);
        $this->assertIsFloat($snapshot->startupTime);

        // startupTime in snapshot is stored as seconds (float), derived from integer monotonic duration
        $expectedSeconds = $diag->startupTime / 1e6;
        $this->assertEqualsWithDelta($expectedSeconds, $snapshot->startupTime, 0.05, 'startup time should be converted to seconds');
    }
}