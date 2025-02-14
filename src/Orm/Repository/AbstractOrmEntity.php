<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Entity\AbstractEntity;
use Charcoal\App\Kernel\Orm\Entity\CacheableEntityInterface;

/**
 * Class AbstractOrmEntity
 * @package Charcoal\App\Kernel\Orm\Repository
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