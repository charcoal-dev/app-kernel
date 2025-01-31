<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity\Exception;

use Charcoal\App\Kernel\Entity\AbstractEntity;

/**
 * Class ChecksumComputeException
 * @package Charcoal\App\Kernel\Entity\Exception
 */
class ChecksumComputeException extends \Exception
{
    public readonly string $entityClass;
    public readonly int|string|null $entityId;

    public function __construct(AbstractEntity $entity, \Throwable $previous)
    {
        $this->entityClass = get_class($entity);
        $this->entityId = $entity->getPrimaryId();
        parent::__construct(sprintf(
            'Caught "%s" while computing checksum for "%s" with ID "%s"',
            $previous::class,
            $this->entityClass,
            $this->entityId ?? "[unknown]"
        ), 0, previous: $previous);
    }
}