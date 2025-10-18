<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\App\Kernel\Enums\SecretsStoreType;
use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\App\Kernel\Internal\Security\SecretsStoreConfig;
use Charcoal\App\Kernel\Internal\Security\SemaphoreProviderConfig;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Security\Secrets\Enums\KeySize;

/**
 * Represents a configuration object for managing security-related settings.
 */
final readonly class SecurityConfig implements ConfigSnapshotInterface
{
    use NoDumpTrait;

    /** @var array<string, SemaphoreProviderConfig> */
    public array $semaphores;
    /** @var array<string, SecretsStoreConfig> */
    public array $secretsStores;

    /**
     * @param array<string, array{SemaphoreProviderEnumInterface, string}> $semaphores
     * @param array<string, array{SecretsStoreEnumInterface, string}> $secretsStores
     */
    public function __construct(array $semaphores, array $secretsStores)
    {
        // Semaphore Providers
        $semaphoreProviders = [];
        foreach ($semaphores as $semaphore) {
            [$providerEnum, $pathOrNode] = $semaphore;

            // Dedupe
            if (isset($semaphoreProviders[$providerEnum->getConfigKey()])) {
                throw new \DomainException(
                    sprintf("Semaphore provider [%s] already declared", $providerEnum->getConfigKey()));
            }

            // Empty name?
            if (!$pathOrNode) {
                throw new \InvalidArgumentException("Semaphore path or node is missing: "
                    . $providerEnum->getConfigKey());
            }

            // Bind with path or node
            if ($providerEnum->getType() === SemaphoreType::LFS) {
                // Validate the local filesystem path as directory
                try {
                    $semaphoreDirectory = new DirectoryPath($pathOrNode);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException("Invalid path to semaphore directory: "
                        . $providerEnum->getConfigKey(), previous: $e);
                }

                $semaphoreDirectoryWritable = match (DIRECTORY_SEPARATOR) {
                    "\\" => $semaphoreDirectory->writable,
                    default => $semaphoreDirectory->writable && $semaphoreDirectory->executable,
                };

                if (!$semaphoreDirectoryWritable) {
                    throw new \DomainException("Semaphore directory must be writable: "
                        . $semaphoreDirectory->absolute);
                }

                $semaphoreProviders[$providerEnum->getConfigKey()] = new SemaphoreProviderConfig($providerEnum, $semaphoreDirectory);
                unset($semaphoreDirectory, $semaphoreDirectoryWritable);
            } else {
                // Store string pointer as-is
                $semaphoreProviders[$providerEnum->getConfigKey()] = new SemaphoreProviderConfig($providerEnum, $pathOrNode);
            }
        }

        $this->semaphores = $semaphoreProviders;

        // Secrets stores
        $secretsProviders = [];
        foreach ($secretsStores as $secretsStore) {
            [$storeEnum, $pathOrNode, $keySize] = $secretsStore;

            // Dedupe
            if (isset($secretsProviders[$storeEnum->getConfigKey()])) {
                throw new \DomainException(
                    sprintf("Secrets store [%s] already declared", $storeEnum->getConfigKey()));
            }

            // Empty name?
            if (!$pathOrNode) {
                throw new \InvalidArgumentException("Secrets store path or node is missing: "
                    . $storeEnum->getConfigKey());
            }

            if (!$keySize instanceof KeySize) {
                throw new \InvalidArgumentException("Invalid key size for secrets store: " . $storeEnum->getConfigKey());
            }

            // Bind with path or node
            if ($storeEnum->getStoreType() === SecretsStoreType::LFS) {
                // Validate the local filesystem path as directory
                try {
                    $secretsDirectory = new DirectoryPath($pathOrNode);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException("Invalid path to secrets directory: "
                        . $storeEnum->getConfigKey(), previous: $e);
                }

                if (!$secretsDirectory->readable) {
                    throw new \DomainException("Secrets directory must be readable: "
                        . $secretsDirectory->absolute);
                }

                $secretsProviders[$storeEnum->getConfigKey()] = new SecretsStoreConfig($storeEnum, $secretsDirectory, $keySize);
                unset($secretsDirectory);
            } else {
                throw new \DomainException("Secrets store type not supported: " . $storeEnum->getStoreType()->name);
            }
        }

        $this->secretsStores = $secretsProviders;
    }
}