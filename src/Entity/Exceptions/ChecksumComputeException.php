<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity\Exceptions;

use Charcoal\App\Kernel\Entity\AbstractEntity;

/**
 * Class ChecksumComputeException
 * @package Charcoal\App\Kernel\Entity\Exception
 */
class ChecksumComputeException extends EntityException
{
    public function __construct(AbstractEntity $entity, \Throwable $previous)
    {
        parent::__construct($entity, previous: $previous);
    }
}