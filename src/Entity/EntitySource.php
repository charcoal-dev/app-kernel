<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

/**
 * Class EntitySource
 * @package Charcoal\App\Kernel\Entity
 */
enum EntitySource: string
{
    case DATABASE = "database";
    case CACHE = "cache";
    case RUNTIME = "runtime";
}