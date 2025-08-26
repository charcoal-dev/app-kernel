<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\EntryPoint\SapiConfigInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;

/**
 * Represents a collection of configurations for SAPI interfaces.
 * This class ensures that all provided configurations implement the SapiConfigInterface.
 * It also guarantees that duplicate configurations for the same interface name are rejected.
 */
final readonly class SapiInterfacesConfig implements ConfigSnapshotInterface
{
    /** @var array<SapiConfigInterface> */
    public array $interfaces;

    /**
     * @param SapiHttpInterfaceConfig ...$configs
     */
    public function __construct(SapiHttpInterfaceConfig ...$configs)
    {
        foreach ($configs as $config) {
            if (isset($this->interfaces[$config->interface->name])) {
                throw new \DomainException(sprintf('SAPI interface config "%s" already registered',
                    $config->interface->name));
            }

            $this->interfaces[$config->interface->name] = $config;
        }
    }
}