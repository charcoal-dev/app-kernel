<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\Buffers\Types\Bytes20;

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
     * Sets current validation state for entity checksum
     * @param bool $state
     * @return void
     */
    public function setChecksumValidation(bool $state): void
    {
        $this->entityChecksumValidated = $state;
    }
}