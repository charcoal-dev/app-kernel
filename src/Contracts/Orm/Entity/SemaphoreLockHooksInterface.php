<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Entity;

/**
 * Interface SemaphoreLockHooksInterface
 * @package Charcoal\App\Kernel\Contracts\Orm\Entity
 */
interface SemaphoreLockHooksInterface
{
    public function onLockObtained(): ?string;
}