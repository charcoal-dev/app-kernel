<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\EntryPoint\EntryPointEnumInterface;
use Charcoal\App\Kernel\Contracts\EntryPoint\SapiHttpConfigInterface;
use Charcoal\App\Kernel\Enums\SapiEnum;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\Http\Commons\Support\CorsPolicy;
use Charcoal\Http\Router\Config\RouterConfig;

/**
 * Class SapiHttpInterfaceConfig
 * @package Charcoal\App\Kernel\Config\Snapshot
 */
final readonly class SapiHttpInterfaceConfig implements ConfigSnapshotInterface, SapiHttpConfigInterface
{
    public function __construct(
        public EntryPointEnumInterface $interface,
        public RouterConfig            $routerConfig,
        public CorsPolicy              $corsPolicy
    )
    {
        // Interface/EntryPoint Enum
        if ($this->interface->getType() !== SapiEnum::Http) {
            throw new \InvalidArgumentException("Invalid SAPI type for: " . $interface->name);
        }
    }
}