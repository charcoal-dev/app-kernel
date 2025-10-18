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
use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use Charcoal\Filesystem\Exceptions\InvalidPathException;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Security\Secrets\Enums\KeySize;

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
     * Declare a semaphore provider.
     * @throws InvalidPathException
     * @api
     */
    public function declareSemaphore(SemaphoreProviderEnumInterface $provider, string $pathOrNode): self
    {
        if ($provider->getType() === SemaphoreType::LFS) {
            $pathOrNode = $this->checkPrefixDirectoryPath($pathOrNode);
        }

        $this->semaphores[$provider->name] = [$provider, $pathOrNode];
        return $this;
    }

    /**
     * Declare a secret store provider.
     * @throws InvalidPathException
     * @api
     */
    public function declareSecretStore(SecretsStoreEnumInterface $provider, string $pathOrNode, KeySize $keySize): self
    {
        $pathOrNode = $this->checkPrefixDirectoryPath($pathOrNode);
        $this->secretsStores[$provider->name] = [$provider, $pathOrNode, $keySize];
        return $this;
    }

    /**
     * @return SecurityConfig
     */
    public function build(): SecurityConfig
    {
        return new SecurityConfig($this->semaphores, $this->secretsStores);
    }

    /**
     * @param string $path
     * @return string
     * @throws \Charcoal\Filesystem\Exceptions\InvalidPathException
     */
    private function checkPrefixDirectoryPath(string $path): string
    {
        if (DIRECTORY_SEPARATOR === "\\") {
            if (!preg_match("/^[a-zA-Z]:/", $path)) {
                return $this->root->join($path)->path;
            }
        }

        if (!str_starts_with($path, "/")) {
            return $this->root->join($path)->path;
        }

        return $path;
    }
}