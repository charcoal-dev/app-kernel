<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Enums;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;

/**
 * Class CacheStores
 * @package Charcoal\Tests\App\Fixtures\Enums
 */
enum CacheStores implements CacheStoreEnumInterface
{
    case Primary;
    case Secondary;

    public function getConfigKey(): string
    {
        return $this->name;
    }
}