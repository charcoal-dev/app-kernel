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
     * @param SecretsStoreEnumInterface $scope
     * @return SecretStorageInterface
     */
    public function get(SecretsStoreEnumInterface $scope): SecretStorageInterface
    {
        return $this->getExistingOrCreate($scope->getConfigKey());
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
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->instances = [];
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