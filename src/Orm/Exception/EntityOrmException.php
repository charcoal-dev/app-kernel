<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Exception;

use Charcoal\Database\ORM\Exception\OrmException;
use Charcoal\OOP\OOP;

/**
 * Class EntityOrmException
 * @package Charcoal\App\Kernel\Orm\Exception
 */
class EntityOrmException extends \Exception
{
    public function __construct(
        string                 $className,
        public readonly string $entityId,
        OrmException           $exception
    )
    {
        parent::__construct(
            OOP::baseClassName($className) . ' caught "' . $exception::class . '"',
            previous: $exception
        );
    }
}