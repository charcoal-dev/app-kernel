<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity\Exceptions;

use Charcoal\App\Kernel\Entity\AbstractEntity;
use Charcoal\Base\Support\Helpers\ObjectHelper;

/**
 * Class EntityException
 * @package Charcoal\App\Kernel\Entity\Exceptions
 */
class EntityException extends \Exception
{
    public function __construct(
        public readonly AbstractEntity $entity,
        ?\Throwable                    $previous = null,
        string                         $message = "",
        int                            $code = 0,
    )
    {
        parent::__construct(
            $message ?: ($previous ?
                sprintf('Caught "%s" for "%s" with ID "%s"',
                    ObjectHelper::baseClassName($previous::class),
                    ObjectHelper::baseClassName($this->entity::class),
                    $this->entity->getPrimaryId() ?? "[unknown]") :
                $this->entity::class),
            $code,
            $previous
        );
    }
}