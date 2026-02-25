<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\App\Kernel\Contracts\Security\ConcurrencyResourceLockInterface;
use Charcoal\App\Kernel\Contracts\Security\SecurityModuleInterface;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;
use Charcoal\Semaphore\Exceptions\SemaphoreLockException;

/**
 * Provides mechanisms to manage concurrent resource locks.
 * This class is responsible for handling the creation, acquisition,
 * and release of resource locks using a semaphore service. It ensures
 * that shared resources are accessed in a thread-safe manner.
 */
final class ConcurrencyLocks implements SecurityModuleInterface
{
    private readonly SemaphoreService $semaphore;

    /** @var SemaphoreLockInterface[] */
    private array $locks = [];

    public function __construct(private readonly ?SemaphoreProviderEnumInterface $providerEnum)
    {
    }

    /**
     * @throws SemaphoreLockException
     */
    public function acquireLock(
        ConcurrencyResourceLockInterface $resource,
        bool                             $waitForLock = false,
        bool                             $setAutoRelease = true,
        float                            $checkInterval = 0.25,
        int                              $maxWaiting = 6
    ): SemaphoreLockInterface
    {
        if (!$this->providerEnum) {
            throw new \RuntimeException("ConcurrencyLocks: Semaphore provider not set");
        }

        $lockId = $resource->concurrencyResourceLockId();
        $existing = $this->locks[$lockId] ?? null;
        if ($existing) {
            if ($existing->isLocked()) {
                return $existing;
            }

            unset($this->locks[$lockId]);
        }

        $resourceLock = $this->semaphore->lock($this->providerEnum, $lockId,
            checkInterval: $waitForLock ? $checkInterval : 0,
            maximumWait: $waitForLock ? $maxWaiting : 0
        );

        $this->locks[$lockId] = $resourceLock;
        if ($setAutoRelease) {
            $resourceLock->setAutoRelease();
        }

        return $resourceLock;
    }

    /**
     * @param SecurityService $securityService
     * @return void
     */
    public function bootstrap(SecurityService $securityService): void
    {
        $this->semaphore = $securityService->semaphore;
    }

    /**
     * @return array
     */
    protected function collectSerializableData(): array
    {
        return [];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
    }
}