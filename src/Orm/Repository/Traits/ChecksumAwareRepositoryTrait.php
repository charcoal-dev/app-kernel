<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Entity\ChecksumAwareEntityInterface;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\Buffers\Types\Bytes20;
use Charcoal\Cipher\Cipher;

/**
 * Trait ChecksumAwareRepositoryTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 */
trait ChecksumAwareRepositoryTrait
{
    private ?Cipher $cipher = null;

    /**
     * @return Cipher
     */
    public function getCipher(): Cipher
    {
        if (!$this->cipher) {
            $this->cipher = $this->module->getCipherFor($this);
            if (!$this->cipher) {
                throw new \LogicException("No cipher resolved for " . static::class);
            }
        }
        return $this->cipher;
    }

    /**
     * @param OrmEntityBase|null $entity
     * @return Bytes20
     * @throws \Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException
     */
    protected function entityChecksumCalculate(OrmEntityBase $entity = null): Bytes20
    {
        return $this->isChecksumAware($entity)
            ->calculateChecksum($this->getCipher(), $this->entityChecksumIterations);
    }

    /**
     * @param OrmEntityBase|null $entity
     * @return string
     */
    public function entityChecksumRawString(OrmEntityBase $entity = null): string
    {
        return $this->isChecksumAware($entity)->checksumRawString();
    }

    /**
     * @param OrmEntityBase|null $entity
     * @return bool
     * @throws \Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException
     */
    protected function entityChecksumVerify(OrmEntityBase $entity = null): bool
    {
        return $this->isChecksumAware($entity)
            ->verifyChecksum($this->getCipher(), $this->entityChecksumIterations);
    }

    /**
     * @param OrmEntityBase|null $entity
     * @return void
     * @throws \Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException
     * @throws \Charcoal\App\Kernel\Entity\Exceptions\ChecksumMismatchException
     */
    protected function entityChecksumValidate(OrmEntityBase $entity = null): void
    {
        $this->isChecksumAware($entity)
            ->validateChecksum($this->getCipher(), $this->entityChecksumIterations);
    }

    /**
     * @param OrmEntityBase|null $entity
     * @return ChecksumAwareEntityInterface
     */
    private function isChecksumAware(OrmEntityBase $entity = null): ChecksumAwareEntityInterface
    {
        if (!$entity instanceof ChecksumAwareEntityInterface) {
            throw new \LogicException(static::class . " does not implement ChecksumAwareEntityInterface");
        }

        return $entity;
    }
}