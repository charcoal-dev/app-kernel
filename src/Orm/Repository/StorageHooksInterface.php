<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

/**
 * Interface EntityLifecycleHooksInterface
 * @package Charcoal\App\Kernel\Orm\Repository
 */
interface StorageHooksInterface
{
    public function onRetrieve(EntitySource $source): ?string;

    public function onCacheStore(): ?string;
}