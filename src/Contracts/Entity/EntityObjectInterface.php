<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Entity;

/**
 * Interface EntityObjectInterface
 */
interface EntityObjectInterface
{
    /**
     * Returns identifier a UUID or UID or ID or identifier of sorts, for instance
     * @return int|string|null
     */
    public function getPrimaryId(): int|string|null;
}