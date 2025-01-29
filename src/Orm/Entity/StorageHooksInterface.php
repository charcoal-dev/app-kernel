<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

use Charcoal\App\Kernel\Orm\Repository\EntitySource;

/**
 * Interface StorageHooksInterface
 * @package Charcoal\App\Kernel\Orm\Entity
 */
interface StorageHooksInterface
{
    public function onRetrieve(EntitySource $source): ?string;

    public function onCacheStore(): ?string;
}