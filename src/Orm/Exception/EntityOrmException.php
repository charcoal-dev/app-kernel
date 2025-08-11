<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Exception;

use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Support\ObjectHelper;
use Charcoal\Database\ORM\Exception\OrmException;

/**
 * Class EntityOrmException
 * @package Charcoal\App\Kernel\Orm\Exception
 */
class EntityOrmException extends WrappedException
{
    public function __construct(string $className, \Throwable $previous)
    {
        parent::__construct($previous, ObjectHelper::baseClassName($className) . ' caught "' .
            ($previous instanceof OrmException ? ObjectHelper::baseClassName($previous::class) : $previous::class) . '"');
    }
}