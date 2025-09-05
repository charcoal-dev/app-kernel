<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException;
use Charcoal\App\Kernel\Entity\Exceptions\ChecksumMismatchException;
use Charcoal\Buffers\Types\Bytes20;
use Charcoal\Cipher\Cipher;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use Charcoal\Contracts\Vectors\StringVectorInterface;

/**
 * Trait ChecksumAwareTrait
 * @package Charcoal\App\Kernel\Entity
 * @mixin AbstractEntity
 */
trait ChecksumAwareEntityTrait
{
    public bool $entityChecksumValidated = false;

    /**
     * Return array of data for computing checksum hash
     * @return array
     */
    abstract public function collectChecksumData(): array;

    /**
     * Return existing checksum Byte20 frame or NULL
     * @return Bytes20|null
     */
    abstract public function getChecksum(): ?Bytes20;

    /**
     * @param Cipher $cipher
     * @param int $iterations
     * @return Bytes20
     * @throws ChecksumComputeException
     */
    public function calculateChecksum(Cipher $cipher, int $iterations): Bytes20
    {
        try {
            // Todo: replace
            return new Bytes20(hash("sha1", $this->checksumRawString(), true));
        } catch (\Throwable $t) {
            throw new ChecksumComputeException($this, $t);
        }
    }

    /**
     * @return string
     * @throws ChecksumComputeException
     */
    public function checksumRawString(): string
    {
        $checksumData = $this->collectChecksumData();
        $checksumValues = [];
        foreach ($checksumData as $key => $value) {
            try {
                $this->checksumDataValue($key, $value, $checksumValues);
            } catch (\Throwable $t) {
                throw new ChecksumComputeException($this, $t);
            }
        }

        return implode(":", $checksumValues);
    }

    /**
     * Calculates checksum and matches it with argument
     * @param Cipher $cipher
     * @param int $iterations
     * @return bool
     * @throws ChecksumComputeException
     */
    public function verifyChecksum(Cipher $cipher, int $iterations): bool
    {
        return $this->entityChecksumValidated =
            $this->calculateChecksum($cipher, $iterations)->equals($this->getChecksum() ?? "\0");
    }

    /**
     * Validates checksum and THROWS if they do not match
     * @param Cipher $cipher
     * @param int $iterations
     * @return void
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    public function validateChecksum(Cipher $cipher, int $iterations): void
    {
        if (!$this->verifyChecksum($cipher, $iterations)) {
            throw new ChecksumMismatchException($this);
        }
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