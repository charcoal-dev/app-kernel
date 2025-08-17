<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Entity;

use Charcoal\Base\Enums\FetchOrigin;

/**
 * Interface StorageHooksInterface
 * @package Charcoal\App\Kernel\Contracts\Orm\Entity
 */
interface StorageHooksInterface
{
    public function onRetrieve(FetchOrigin $origin): ?string;

    public function onCacheStore(): ?string;
}