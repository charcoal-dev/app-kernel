<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Security;

/**
 * Interface that defines a contract for managing a concurrency resource lock.
 * Provides a method to retrieve the identifier associated with the lock.
 */
interface ConcurrencyResourceLockInterface
{
    /**
     * @return string
     */
    public function concurrencyResourceLockId(): string;
}