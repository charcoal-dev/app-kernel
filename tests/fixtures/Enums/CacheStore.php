<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Enums;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Base\Enums\Traits\EnumMappingTrait;

/**
 * Class CacheStores
 * @package Charcoal\Tests\App\Fixtures\Enums
 */
enum CacheStore implements CacheStoreEnumInterface
{
    case Primary;
    case Secondary;

    use EnumMappingTrait;

    public function getConfigKey(): string
    {
        return $this->name;
    }

    public static function find(string $key): self
    {
        foreach (self::cases() as $case) {
            if ($case->getConfigKey() === $key) {
                return $case;
            }
        }

        throw new \InvalidArgumentException("Cache store not found: " . $key);
    }
}