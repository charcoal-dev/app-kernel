<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\App\Kernel\Contracts\Security\SecurityModuleInterface;
use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Registry\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Registry\Traits\RegistryKeysLowercaseTrimmed;
use Charcoal\Contracts\Security\Secrets\SecretStorageInterface;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Security\Secrets\Filesystem\SecretsDirectory;
use Charcoal\Security\Secrets\Support\SecretKeyRef;
use Charcoal\Security\Secrets\Types\AbstractSecretKey;

/**
 * @template-extends AbstractFactoryRegistry<SecretStorageInterface>
 */
final class SecretsService extends AbstractFactoryRegistry implements SecurityModuleInterface
{
    use RegistryKeysLowercaseTrimmed;
    use ControlledSerializableTrait;
    use NoDumpTrait;
    use NotCloneableTrait;

    private readonly SecurityService $securityService;

    /** @var array<string, AbstractSecretKey> */
    private array $loadedSecrets = [];

    /**
     * @param SecurityService $securityService
     * @return void
     * @internal
     */
    public function bootstrap(SecurityService $securityService): void
    {
        $this->securityService = $securityService;
    }

    /**
     * @param SecretsStoreEnumInterface $store
     * @return SecretStorageInterface
     */
    public function getStore(SecretsStoreEnumInterface $store): SecretStorageInterface
    {
        return $this->getExistingOrCreate($store->getConfigKey());
    }

    /**
     * @param SecretsStoreEnumInterface $store
     * @param SecretKeyRef $keyRef
     * @return AbstractSecretKey
     */
    public function resolveSecretKey(SecretsStoreEnumInterface $store, SecretKeyRef $keyRef): AbstractSecretKey
    {
        $normalized = $this->normalizeSecretKeyId($store, $keyRef->toString());
        if (isset($this->loadedSecrets[$normalized])) {
            return $this->loadedSecrets[$normalized];
        }

        $secretStore = $this->getStore($store);

        // Remixed Key?
        if ($keyRef->remixMessage) {
            $parentKey = $this->resolveSecretKey($store, $keyRef->withRemixing(null, null));
            $remixedSecret = $parentKey->remixEntropy($keyRef->remixMessage, $keyRef->remixIterations);
            $this->loadedSecrets[$normalized] = $remixedSecret;
            return $remixedSecret;
        }

        // Normal Secret Key
        /** @var AbstractSecretKey $secretKey */
        $secretKey = $secretStore->load(
            $keyRef->ref,
            $keyRef->version,
            $keyRef->namespace,
            allowNullPadding: false
        );

        $this->loadedSecrets[$normalized] = $secretKey;
        return $secretKey;
    }

    /**
     * @param SecretsStoreEnumInterface $store
     * @param string $keyId
     * @return string
     */
    private function normalizeSecretKeyId(SecretsStoreEnumInterface $store, string $keyId): string
    {
        return strtolower($store->name . "#" . $keyId);
    }

    /**
     * @param string $key
     * @return SecretStorageInterface
     * @throws WrappedException
     */
    protected function create(string $key): SecretStorageInterface
    {
        $secretConfig = $this->securityService->config->secretsStores[$key] ?? null;
        if (!$secretConfig) {
            throw new \InvalidArgumentException("No secrets store config found for key: " . $key);
        }

        // Semaphore Type resolution
        // LFS:
        if ($secretConfig->ref instanceof DirectoryPath) {
            try {
                return new SecretsDirectory($secretConfig);
            } catch (\Exception $e) {
                throw new WrappedException($e, "Failed to resolve directory for semaphore scope: " . $key);
            }
        }

        throw new \InvalidArgumentException("Unsupported secrets type: " . $secretConfig->provider->name);
    }

    /**
     * @return DirectoryPath[]
     */
    protected function collectSerializableData(): array
    {
        return [
            "instances" => null,
            "loadedSecrets" => null
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->instances = [];
        $this->loadedSecrets = [];
    }

    /**
     * @return array
     */
    protected function unserializeDependencies(): array
    {
        return [
            self::class,
        ];
    }
}