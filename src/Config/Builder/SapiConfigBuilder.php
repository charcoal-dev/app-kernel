<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\SapiConfigBundle;
use Charcoal\App\Kernel\Contracts\ServerApi\ServerApiEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;

/**
 * Responsible for building and managing the configuration of Server Application Programming Interface (SAPI) interfaces.
 * Implements the ConfigBuilderInterface to provide methods for configuring HTTP SAPI interfaces and finalizing
 * the configuration into a snapshot.
 */
final class SapiConfigBuilder implements ConfigBuilderInterface
{
    /** @var array<HttpServerConfigBuilder> */
    private array $config = [];

    public function __construct()
    {
    }

    /**
     * @param ServerApiEnumInterface $interface
     * @return HttpServerConfigBuilder
     */
    public function http(ServerApiEnumInterface $interface): HttpServerConfigBuilder
    {
        $config = new HttpServerConfigBuilder($interface);
        $this->include($config);
        return $config;
    }

    /**
     * @param HttpServerConfigBuilder $builder
     * @return void
     */
    private function include(HttpServerConfigBuilder $builder): void
    {
        if (isset($this->config[$builder->interface->name])) {
            throw new \InvalidArgumentException("HTTP SAPI interface already configured: " .
                $builder->interface->name);
        }

        $this->config[$builder->interface->name] = $builder;
    }

    /**
     * Builds and returns a configuration snapshot based on the current configuration state.
     */
    public function build(): SapiConfigBundle
    {
        $built = [];
        foreach ($this->config as $config) {
            $built[] = $config->build();
        }

        return new SapiConfigBundle(...$built);
    }
}