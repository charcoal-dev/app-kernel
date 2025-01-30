<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException;
use Charcoal\App\Kernel\Entity\Exception\ChecksumMismatchException;
use Charcoal\Buffers\Frames\Bytes20;
use Charcoal\Cipher\Cipher;

/**
 * Interface ChecksumAwareInterface
 * @package Charcoal\App\Kernel\Entity
 */
interface ChecksumAwareEntityInterface
{
    public function collectChecksumData(): array;

    public function getChecksum(): ?Bytes20;

    /**
     * @param Cipher $cipher
     * @param int $iterations
     * @return Bytes20
     * @throws ChecksumComputeException
     */
    public function calculateChecksum(Cipher $cipher, int $iterations): Bytes20;

    /**
     * @param Cipher $cipher
     * @param int $iterations
     * @return bool
     * @throws ChecksumComputeException
     */
    public function verifyChecksum(Cipher $cipher, int $iterations): bool;

    /**
     * @param Cipher $cipher
     * @param int $iterations
     * @return void
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    public function validateChecksum(Cipher $cipher, int $iterations): void;
}