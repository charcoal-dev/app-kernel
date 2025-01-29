<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\Buffers\Frames\Bytes20;
use Charcoal\Cipher\Cipher;

/**
 * Interface ChecksumAwareInterface
 * @package Charcoal\App\Kernel\Entity
 */
interface ChecksumAwareEntityInterface
{
    function collectChecksumData(): array;

    function getChecksum(): ?Bytes20;

    public function calculateChecksum(Cipher $cipher, int $iterations): Bytes20;

    public function verifyChecksum(Cipher $cipher, int $iterations): bool;

    public function validateChecksum(Cipher $cipher, int $iterations): void;
}