<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\StorageHooks;

use Charcoal\App\Kernel\Entity\EntitySource;

/**
 * Interface StorageHooksInterface
 * @package Charcoal\App\Kernel\Contracts\StorageHooks
 */
interface StorageHooksInterface
{
    public function onRetrieve(EntitySource $source): ?string;

    public function onCacheStore(): ?string;
}