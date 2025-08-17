<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Repository;

use Charcoal\App\Kernel\Entity\Exceptions\ChecksumComputeException;
use Charcoal\App\Kernel\Entity\Exceptions\ChecksumMismatchException;
use Charcoal\Buffers\Frames\Bytes20;
use Charcoal\Cipher\Cipher;

/**
 * Interface ChecksumAwareRepositoryInterface
 * @package Charcoal\App\Kernel\Contracts\Orm\Repository
 */
interface ChecksumAwareRepositoryInterface
{
    /**
     * @return Cipher
     */
    public function getCipher(): Cipher;

    /**
     * @return Bytes20
     * @throws ChecksumComputeException
     */
    public function calculateChecksum(): Bytes20;

    /**
     * @return string
     */
    public function entityChecksumRawString(): string;

    /**
     * @return bool
     * @throws ChecksumComputeException
     */
    public function verifyChecksum(): bool;

    /**
     * @return void
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    public function validateChecksum(): void;
}