<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Concurrency\Locks;

use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;

/**
 * Represents configuration options for acquiring a concurrency lock.
 */
final readonly class LockAcquireOptions
{
    public function __construct(
        public SemaphoreProviderEnumInterface $providerEnum,
        public string                         $resourceId,
        public bool                           $waitForLock = false,
        public bool                           $setAutoRelease = true,
        public float                          $checkInterval = 0.25,
        public int                            $maxWaiting = 6
    )
    {

    }
}