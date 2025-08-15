<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\Database\DbCredentials;

/**
 * Class DbConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 * @extends AbstractConfigCollector<DatabaseEnumInterface, DbCredentials>
 */
final class DbConfigBuilder extends AbstractConfigCollector
{
    /**
     * @param DatabaseEnumInterface $key
     * @param DbCredentials $config
     * @return void
     */
    public function set(DatabaseEnumInterface $key, DbCredentials $config): void
    {
        $this->storeConfig($key, $config);
    }
}