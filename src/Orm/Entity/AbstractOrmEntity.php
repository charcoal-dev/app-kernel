<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Entity;

use Charcoal\App\Kernel\Contracts\Orm\Entity\CacheableEntityInterface;
use Charcoal\App\Kernel\Entity\AbstractEntity;

/**
 * Class AbstractOrmEntity
 * @package Charcoal\App\Kernel\Orm\Entity
 */
abstract class AbstractOrmEntity extends AbstractEntity
{
    /**
     * @return array
     */
    final public function __serialize(): array
    {
        $data = parent::__serialize();
        if ($this instanceof CacheableEntityInterface) {
            $data["entityCachedOn"] = $this->getCachedOn();
        }

        return $data;
    }
}