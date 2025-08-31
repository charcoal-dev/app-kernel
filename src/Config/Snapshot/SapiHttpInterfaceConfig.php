<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\Enums\SapiEnumInterface;
use Charcoal\App\Kernel\Enums\SapiType;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\Http\Server\Config\ServerConfig;

/**
 * This class enforces that the provided SAPI interface type is of an HTTP type.
 * It captures and stores the specific interface and related server configuration instances.
 * Implements the ConfigSnapshotInterface.
 */
final readonly class SapiHttpInterfaceConfig implements ConfigSnapshotInterface
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