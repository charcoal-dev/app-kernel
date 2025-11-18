<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Orm\Entity;

use Charcoal\Buffers\Types\Bytes20;

/**
 * Interface ChecksumAwareInterface
 * @package Charcoal\App\Kernel\Entity
 */
interface ChecksumAwareEntityInterface
{
    public function collectChecksumData(): array;

    public function getChecksum(): ?Bytes20;

    public function setChecksumValidation(bool $state): void;
}