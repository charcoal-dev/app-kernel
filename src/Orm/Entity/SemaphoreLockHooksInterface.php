<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

/**
 * Interface SemaphoreLockHooksInterface
 * @package Charcoal\App\Kernel\Orm\Entity
 */
interface SemaphoreLockHooksInterface
{
    public function onLockObtained(): ?string;
}