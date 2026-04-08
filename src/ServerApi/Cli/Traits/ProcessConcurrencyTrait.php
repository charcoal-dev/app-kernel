<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli\Traits;

use Charcoal\App\Kernel\Concurrency\Locks\LockAcquireOptions;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * Provides functionality for managing process concurrency using semaphore locks.
 */
trait ProcessConcurrencyTrait
{
    public readonly ?LockAcquireOptions $semaphorePolicy;
    public readonly ?SemaphoreLockInterface $semaphoreLock;

    /**
     * @return void
     * @throws \Charcoal\App\Kernel\Concurrency\ConcurrencyLockException
     */
    private function initializeConcurrency(): void
    {
        $this->semaphorePolicy = $this->declareSemaphorePolicy();
        $this->semaphoreLock = $this->semaphorePolicy ?
            $this->cli->app->concurrency->acquireLock($this->semaphorePolicy) : null;
    }

    /**
     * @return LockAcquireOptions|null
     */
    protected function declareSemaphorePolicy(): ?LockAcquireOptions
    {
        return null;
    }
}