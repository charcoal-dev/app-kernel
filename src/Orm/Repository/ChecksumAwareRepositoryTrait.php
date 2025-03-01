<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Entity\ChecksumAwareEntityInterface;
use Charcoal\Buffers\Frames\Bytes20;

/**
 * Trait ChecksumAwareRepositoryTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait ChecksumAwareRepositoryTrait
{
    /**
     * @param AbstractOrmEntity|null $entity
     * @return Bytes20
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException
     */
    protected function entityChecksumCalculate(AbstractOrmEntity $entity = null): Bytes20
    {
        return $this->isChecksumAware($entity)
            ->calculateChecksum($this->getCipher(), $this->entityChecksumIterations);
    }

    /**
     * @param AbstractOrmEntity|null $entity
     * @return string
     */
    public function entityChecksumRawString(AbstractOrmEntity $entity = null): string
    {
        return $this->isChecksumAware($entity)->checksumRawString();
    }

    /**
     * @param AbstractOrmEntity|null $entity
     * @return bool
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException
     */
    protected function entityChecksumVerify(AbstractOrmEntity $entity = null): bool
    {
        return $this->isChecksumAware($entity)
            ->verifyChecksum($this->getCipher(), $this->entityChecksumIterations);
    }

    /**
     * @param AbstractOrmEntity|null $entity
     * @return void
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException
     * @throws \Charcoal\App\Kernel\Entity\Exception\ChecksumMismatchException
     */
    protected function entityChecksumValidate(AbstractOrmEntity $entity = null): void
    {
        $this->isChecksumAware($entity)
            ->validateChecksum($this->getCipher(), $this->entityChecksumIterations);
    }

    /**
     * @param AbstractOrmEntity|null $entity
     * @return ChecksumAwareEntityInterface
     */
    private function isChecksumAware(AbstractOrmEntity $entity = null): ChecksumAwareEntityInterface
    {
        if (!$entity instanceof ChecksumAwareEntityInterface) {
            throw new \LogicException(static::class . " does not implement ChecksumAwareEntityInterface");
        }

        return $entity;
    }
}