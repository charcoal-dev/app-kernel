<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Entity\AbstractEntity;
use Charcoal\App\Kernel\Orm\AbstractOrmRepository;

/**
 * Class AbstractOrmEntity
 * @package Charcoal\App\Kernel\Orm\Repository
 */
abstract class AbstractOrmEntity extends AbstractEntity
{
    /**
     * @param AbstractOrmRepository|null $handler
     * @return string
     */
    final public function getIdentifier(AbstractOrmRepository $handler = null): string
    {
        return $handler->getEntityId($this);
    }
}