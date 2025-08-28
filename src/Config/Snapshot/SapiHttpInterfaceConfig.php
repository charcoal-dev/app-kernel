<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\EntryPoint\SapiHttpConfigInterface;
use Charcoal\App\Kernel\Contracts\Enums\SapiEnumInterface;
use Charcoal\App\Kernel\Enums\SapiType;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\Http\Server\Config\ServerConfig;

/**
 * Class SapiHttpInterfaceConfig
 * @package Charcoal\App\Kernel\Config\Snapshot
 */
final readonly class SapiHttpInterfaceConfig implements ConfigSnapshotInterface, SapiHttpConfigInterface
{
    public function __construct(
        public SapiEnumInterface $interface,
        public ServerConfig      $routerConfig
    )
    {
        // Interface/EntryPoint Enum
        if ($this->interface->getType() !== SapiType::Http) {
            throw new \InvalidArgumentException("Invalid SAPI type for: " . $interface->name);
        }
    }
}