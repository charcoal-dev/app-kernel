<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

/**
 * Class EntitySource
 * @package Charcoal\App\Kernel\Orm\Repository
 */
enum EntitySource: string
{
    case DATABASE = "database";
    case CACHE = "cache";
    case RUNTIME = "runtime";
}