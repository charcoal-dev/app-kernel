<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Exception;

use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Database\Orm\Exception\OrmException;

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