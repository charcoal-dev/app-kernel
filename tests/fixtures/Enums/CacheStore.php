<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Enums;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\Base\Enums\Traits\EnumFindCaseTrait;

/**
 * Class CacheStores
 * @package Charcoal\Tests\App\Fixtures\Enums
 */
enum CacheStore implements CacheStoreEnumInterface
{
    case Primary;
    case Secondary;

    use EnumFindCaseTrait;

    public function getConfigKey(): string
    {
        return $this->name;
    }
}