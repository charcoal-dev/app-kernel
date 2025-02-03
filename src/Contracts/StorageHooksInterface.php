<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

use Charcoal\App\Kernel\Entity\EntitySource;

/**
 * Interface StorageHooksInterface
 * @package Charcoal\App\Kernel\Entity
 */
interface StorageHooksInterface
{
    public function onRetrieve(EntitySource $source): ?string;

    public function onCacheStore(): ?string;
}