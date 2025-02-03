<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

/**
 * Interface StaticObjectStoreContract
 * @package Charcoal\App\Kernel\Contracts
 */
interface StaticObjectStoreContract
{
    public const bool ENCRYPTION = false;

    public static function childClasses(): array;

    public static function getObjectStoreKey(): string;
}