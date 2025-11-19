<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Entity\ChecksumAwareEntityInterface;
use Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException;
use Charcoal\App\Kernel\Entity\Exceptions\ChecksumMismatchException;
use Charcoal\App\Kernel\Enums\DigestAlgo;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Repository\OrmRepositoryBase;
use Charcoal\Base\Arrays\ArrayHelper;
use Charcoal\Buffers\Types\Bytes20;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use Charcoal\Contracts\Vectors\StringVectorInterface;

/**
 * Trait ChecksumAwareRepositoryTrait
 * @package Charcoal\App\Kernel\Orm\Repository
 * @use OrmRepositoryBase
 */
trait ChecksumAwareRepositoryTrait
{
    /**
     * @param OrmEntityBase $entity
     * @return string
     * @throws ChecksumComputeException
     */
    protected function entityChecksumRawString(OrmEntityBase $entity): string
    {
        $data = $this->isChecksumAware($entity)->collectChecksumData();
        $data = ArrayHelper::canonicalizeLexicographic($data);
        $checksumItems = [];
        foreach ($data as $key => $value) {
            try {
                $this->checksumDataValue($key, $value, $checksumItems);
            } catch (\Throwable $t) {
                throw new ChecksumComputeException($entity, $t);
            }
        }

        return implode(":", $checksumItems);
    }

    /**
     * @param OrmEntityBase $entity
     * @return Bytes20
     * @throws ChecksumComputeException
     */
    protected function entityChecksumCalculate(OrmEntityBase $entity): Bytes20
    {
        return new Bytes20($this->module->app->security->digest->hmac(
            DigestAlgo::SHA1,
            $this->ensureCipher()->secretKey,
            $this->entityChecksumRawString($entity),
            iterations: 1
        ));
    }

    /**
     * @param OrmEntityBase $entity
     * @return bool
     * @throws ChecksumComputeException
     */
    protected function entityChecksumVerify(OrmEntityBase $entity): bool
    {
        /** @var OrmEntityBase&ChecksumAwareEntityInterface $entity */
        $matches = $this->entityChecksumCalculate($entity)
            ->equals($entity->getChecksum() ?? "\0");
        $entity->setChecksumValidation($matches);
        return $matches;
    }

    /**
     * @param OrmEntityBase $entity
     * @return void
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    protected function entityChecksumValidate(OrmEntityBase $entity): void
    {
        if (!$this->entityChecksumVerify($entity)) {
            throw new ChecksumMismatchException($entity);
        }
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

    /**
     * @param string $key
     * @param mixed $value
     * @param array $checksumData
     * @return void
     */
    private function checksumDataValue(string $key, mixed $value, array &$checksumData): void
    {
        $checksumData[] = match (true) {
            is_string($value), is_int($value), is_float($value) => $value,
            is_null($value) => "",
            is_bool($value) => $value ? 1 : 0,
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \UnitEnum => $value->name,
            $value instanceof ReadableBufferInterface => $value->bytes(),
            $value instanceof StringVectorInterface => $value->join(","),
            $value instanceof \DateTime => $value->getTimestamp(),
            default => throw new \UnexpectedValueException(sprintf(
                'Cannot process value for "%s" of type "%s"',
                $key,
                is_object($value) ? get_class($value) : gettype($value)
            )),
        };
    }
}