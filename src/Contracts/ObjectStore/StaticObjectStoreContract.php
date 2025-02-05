<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\ObjectStore;

/**
 * Interface StaticObjectStoreContract
 * @package Charcoal\App\Kernel\Contracts
 */
interface StaticObjectStoreContract
{
    public const ObjectStoreEncryption ENCRYPTION = ObjectStoreEncryption::DISABLED;

    public static function childClasses(): array;

    public static function getObjectStoreKey(): string;
}