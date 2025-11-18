<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreScopeEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;

/**
 * Class EnumContracts
 * @package Charcoal\App\Kernel\Enums
 */
enum EnumContract
{
    case CacheStoreEnum;
    case DbEnum;
    case SecretsStoreEnum;
    case SemaphoreProviderEnum;
    case SemaphoreScopeEnum;
    case TableRegistryEnum;

    public function getContract(): string
    {
        return match ($this) {
            self::CacheStoreEnum => CacheStoreEnumInterface::class,
            self::DbEnum => DatabaseEnumInterface::class,
            self::SecretsStoreEnum => SecretsStoreEnumInterface::class,
            self::SemaphoreProviderEnum => SemaphoreProviderEnumInterface::class,
            self::SemaphoreScopeEnum => SemaphoreScopeEnumInterface::class,
            self::TableRegistryEnum => TableRegistryEnumInterface::class,
        };
    }
}