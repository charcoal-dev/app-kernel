<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\SecurityConfig;
use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use Charcoal\Filesystem\Path\DirectoryPath;

/**
 * Represents a builder class for constructing security configuration instances.
 * Implements the ConfigBuilderInterface for standardized configuration building.
 */
final class SecurityConfigBuilder implements ConfigBuilderInterface
{
    /** @var array<string, array{SemaphoreProviderEnumInterface, string}> */
    protected array $semaphores = [];
    /** @var array<string, array{SecretsStoreEnumInterface, string}> */
    protected array $secretsStores = [];

    public function __construct(protected DirectoryPath $root)
    {
    }

    /**
     * @api Declare a semaphore provider.
     */
    public function declareSemaphore(SemaphoreProviderEnumInterface $provider, string $pathOrNode): self
    {
        $this->semaphores[$provider->name] = [$provider, $pathOrNode];
        return $this;
    }

    /**
     * @api Declare a secret store provider.
     */
    public function declareSecretStore(SecretsStoreEnumInterface $provider, string $pathOrNode): self
    {
        $this->secretsStores[$provider->name] = [$provider, $pathOrNode];
        return $this;
    }

    /**
     * @return SecurityConfig
     */
    public function build(): SecurityConfig
    {
        return new SecurityConfig($this->semaphores, $this->secretsStores);
    }
}