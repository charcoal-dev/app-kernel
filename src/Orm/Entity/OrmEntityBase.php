<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

use Charcoal\App\Kernel\Contracts\Orm\Entity\CacheableEntityInterface;
use Charcoal\App\Kernel\Entity\AbstractEntity;

/**
 * Class OrmEntityBase
 * @package Charcoal\App\Kernel\Orm\Entity
 */
abstract class OrmEntityBase extends AbstractEntity
{
    /**
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = [];
        if ($this instanceof CacheableEntityInterface) {
            $data["entityCachedOn"] = $this->getCachedOn();
        }

        return $data;
    }
}