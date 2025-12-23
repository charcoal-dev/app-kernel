<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity\Traits;

use Charcoal\Buffers\Types\Bytes20;

/**
 * Trait for entities that can be validated against a checksum hash.
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
     * Sets the current validation state for entity checksum
     * @param bool $state
     * @return void
     */
    public function setChecksumValidation(bool $state): void
    {
        $this->entityChecksumValidated = $state;
    }
}