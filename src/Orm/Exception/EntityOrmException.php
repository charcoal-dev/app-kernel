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
    public function __construct(string $className, \Throwable $exception)
    {
        parent::__construct(
            OOP::baseClassName($className) . ' caught "' .
            ($exception instanceof OrmException ? OOP::baseClassName($exception::class) : $exception::class) . '"',
            previous: $exception
        );
    }
}