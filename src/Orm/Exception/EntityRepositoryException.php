<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Exception;

use Charcoal\App\Kernel\Orm\Repository\OrmRepositoryBase;
use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Database\Orm\Exceptions\OrmException;

/**
 * Class EntityRepositoryException
 * @package Charcoal\App\Kernel\Orm\Exception
 */
class EntityRepositoryException extends WrappedException
{
    public function __construct(OrmRepositoryBase $service, \Throwable $previous)
    {
        parent::__construct($previous, ObjectHelper::baseClassName($service) . ' caught "' .
            ($previous instanceof OrmException ? ObjectHelper::baseClassName($previous::class) : $previous::class) . '"');
    }
}