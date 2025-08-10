<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Exception;

use Charcoal\Base\Exception\WrappedException;
use Charcoal\Database\ORM\Exception\OrmException;
use Charcoal\OOP\OOP;

/**
 * Class EntityOrmException
 * @package Charcoal\App\Kernel\Orm\Exception
 */
class EntityOrmException extends WrappedException
{
    public function __construct(string $className, \Throwable $previous)
    {
        parent::__construct($previous, OOP::baseClassName($className) . ' caught "' .
            ($previous instanceof OrmException ? OOP::baseClassName($previous::class) : $previous::class) . '"');
    }
}