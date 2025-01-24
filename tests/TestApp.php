<?php
declare(strict_types=1);

/**
 * Class TestApp
 *
 * Represents the main application kernel.
 * Extends functionalities from the base AppKernel class in Charcoal framework.
 */
class TestApp extends \Charcoal\App\Kernel\AppKernel
{
    /**
     * Render the configuration object for the application kernel.
     * @return \Charcoal\App\Kernel\Config The kernel configuration instance.
     */
    protected function renderConfig(): \Charcoal\App\Kernel\Config
    {
        return new \Charcoal\App\Kernel\Config(
            \Charcoal\App\Kernel\DateTime\Timezone::UTC,
            new \Charcoal\App\Kernel\Config\CacheConfig(\Charcoal\App\Kernel\Config\CacheDriver::NULL, "", 0, 0),
            new \Charcoal\App\Kernel\Config\DbConfigs()
        );
    }
}