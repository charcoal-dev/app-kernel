<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity\Exception;

use Charcoal\App\Kernel\Entity\AbstractEntity;

/**
 * Class ChecksumMismatchException
 * @package Charcoal\App\Kernel\Entity\Exception
 */
class ChecksumMismatchException extends \Exception
{
    public readonly string $entityClass;
    public readonly int|string|null $entityId;

    public function __construct(AbstractEntity $entity)
    {
        $this->entityClass = get_class($entity);
        $this->entityId = $entity->getIdentifier();
        parent::__construct(
            sprintf('Checksum mismatch for "%s" with ID "%s"', $this->entityClass, $this->entityId ?? "[unknown]"), 0, null);
    }
}