<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Concurrency\Locks\LockAcquireOptions;
use Charcoal\App\Kernel\Contracts\Domain\AppBootstrappableInterface;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\App\Kernel\Security\SemaphoreService;
use Charcoal\Base\Objects\Traits\NotSerializableTrait;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;

/**
 * A service responsible for handling concurrency mechanisms.
 */
final class ConcurrencyService implements AppServiceInterface, AppBootstrappableInterface
{
    use NotSerializableTrait;

    private readonly SemaphoreService $semaphore;
    /** @var SemaphoreLockInterface[] */
    private array $locks = [];

    /**
     * @throws ConcurrencyLockException
     * @api
     */
    public function acquireLock(
        LockAcquireOptions $lockOptions,
    ): SemaphoreLockInterface
    {
        // Check if the lock already exists
        $existing = $this->locks[$lockOptions->resourceId] ?? null;
        if ($existing) {
            if ($existing->isLocked()) {
                return $existing;
            }

            unset($this->locks[$lockOptions->resourceId]);
        }

        // Create a new lock
        try {
            $resourceLock = $this->semaphore->acquireLock(
                $lockOptions->providerEnum,
                $lockOptions->resourceId,
                $lockOptions->waitForLock,
                $lockOptions->checkInterval,
                $lockOptions->maxWaiting
            );

            $this->locks[$lockOptions->resourceId] = $resourceLock;
            if ($lockOptions->setAutoRelease) {
                $resourceLock->setAutoRelease();
            }
        } catch (\Exception $e) {
            throw new ConcurrencyLockException("Failed to acquire lock for resource: " . $lockOptions->resourceId,
                previous: $e);
        }

        return $resourceLock;
    }

    /**
     * @param AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->semaphore = $app->security->semaphore;
    }
}