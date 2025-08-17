<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\AppConfig;
use Charcoal\App\Kernel\Contracts\Enums\TimezoneEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;

/**
 * Class AppConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 */
class AppConfigBuilder implements ConfigBuilderInterface
{
    public function __construct(
        public TimezoneEnumInterface      $timezone,
        public ?CacheConfigObjectsBuilder $cache,
        public ?DbConfigObjectsBuilder    $database,
    )
    {
    }

    public function build(): AppConfig
    {
        return new AppConfig(
            $this->timezone,
            $this->cache?->build(),
            $this->database?->build(),
        );
    }
}