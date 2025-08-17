<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

/**
 * Class DatabasesConfig
 * @package Charcoal\App\Kernel\Config
 */
final readonly class DatabaseManagerConfig
{
    /** @var array<string, DatabaseConfig> */
    public array $configs;

    /**
     * @param array<string, DatabaseConfig> $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }
}