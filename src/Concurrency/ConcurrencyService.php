<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Domain\AppBootstrappableInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
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
        SemaphoreProviderEnumInterface $providerEnum,
        string                         $resourceId,
        bool                           $waitForLock = false,
        bool                           $setAutoRelease = true,
        float                          $checkInterval = 0.25,
        int                            $maxWaiting = 6
    ): SemaphoreLockInterface
    {
        // Check if the lock already exists
        $existing = $this->locks[$resourceId] ?? null;
        if ($existing) {
            if ($existing->isLocked()) {
                return $existing;
            }

            unset($this->locks[$resourceId]);
        }

        // Create a new lock
        try {
            $resourceLock = $this->semaphore->acquireLock($providerEnum, $resourceId,
                $waitForLock, $checkInterval, $maxWaiting);

            $this->locks[$resourceId] = $resourceLock;
            if ($setAutoRelease) {
                $resourceLock->setAutoRelease();
            }
        } catch (\Exception $e) {
            throw new ConcurrencyLockException("Failed to acquire lock for resource: " . $resourceId,
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