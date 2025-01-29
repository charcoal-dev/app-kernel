<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\App\Kernel\Entity\Exception\ChecksumComputeException;
use Charcoal\App\Kernel\Entity\Exception\ChecksumMismatchException;
use Charcoal\Buffers\Frames\Bytes20;

/**
 * Interface ChecksumAwareRepositoryInterface
 * @package Charcoal\App\Kernel\Orm\Repository
 */
interface ChecksumAwareRepositoryInterface
{
    /**
     * @return Bytes20
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    public function calculateChecksum(): Bytes20;

    /**
     * @return bool
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    public function verifyChecksum(): bool;

    /**
     * @return void
     * @throws ChecksumComputeException
     * @throws ChecksumMismatchException
     */
    public function validateChecksum(): void;
}