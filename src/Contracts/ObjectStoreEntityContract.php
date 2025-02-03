<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

/**
 * Interface ObjectStoreEntityContract
 * @package Charcoal\App\Kernel\Contracts
 */
interface ObjectStoreEntityContract
{
    public static function childClasses(): array;

    public static function getObjectStoreKey(): string;
}