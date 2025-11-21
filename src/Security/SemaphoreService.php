<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreScopeEnumInterface;
use Charcoal\App\Kernel\Contracts\Security\SecurityModuleInterface;
use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Registry\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Registry\Traits\RegistryKeysLowercaseTrimmed;
use Charcoal\Cache\Adapters\Redis\Internal\RedisClientInterface;
use Charcoal\Cache\Adapters\Redis\Semaphore\SemaphoreRedis;
use Charcoal\Contracts\Storage\Cache\Adapter\LocksInterface;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Semaphore\SemaphoreDirectory;
use Charcoal\Semaphore\Contracts\SemaphoreLockInterface;
use Charcoal\Semaphore\Contracts\SemaphoreProviderInterface;

/**
 * Provides management for filesystem-based semaphores using a directory-based structure.
 * Uses a factory registry pattern to handle creation and retrieval of semaphores based on scope.
 * Ensures certain traits like no cloning and no dumping are enforced.
 * @template-extends AbstractFactoryRegistry<SemaphoreProviderInterface>
 */
final class SemaphoreService extends AbstractFactoryRegistry implements SecurityModuleInterface
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
     * @param SemaphoreProviderEnumInterface $scope
     * @return SemaphoreProviderInterface
     */
    public function get(SemaphoreProviderEnumInterface $scope): SemaphoreProviderInterface
    {
        return $this->getExistingOrCreate($scope->getConfigKey());
    }

    /**
     * @param SemaphoreProviderEnumInterface|SemaphoreScopeEnumInterface $scope
     * @param string $lockId
     * @param float|null $checkInterval
     * @param int $maximumWait
     * @return SemaphoreLockInterface
     */
    public function lock(
        SemaphoreProviderEnumInterface|SemaphoreScopeEnumInterface $scope,
        string                                                     $lockId,
        ?float                                                     $checkInterval = null,
        int                                                        $maximumWait = 0
    ): SemaphoreLockInterface
    {
        $provider = $scope instanceof SemaphoreScopeEnumInterface ? $scope->provider() : $scope;

        return $this->get($provider)->obtainLock($lockId, $checkInterval, max($maximumWait, 0));
    }

    /**
     * @param string $key
     * @return SemaphoreProviderInterface
     * @throws WrappedException
     */
    protected function create(string $key): SemaphoreProviderInterface
    {
        $semaphoreConfig = $this->securityService->config->semaphores[$key] ?? null;
        if (!$semaphoreConfig) {
            throw new \InvalidArgumentException("No semaphore config found for key: " . $key);
        }

        // Semaphore Type resolution
        // LFS:
        if ($semaphoreConfig->ref instanceof DirectoryPath) {
            try {
                return new SemaphoreDirectory($semaphoreConfig->ref);
            } catch (\Exception $e) {
                throw new WrappedException($e, "Failed to resolve directory for semaphore scope: " . $key);
            }
        }

        // Redis
        if ($semaphoreConfig->provider->getType() === SemaphoreType::Redis) {
            try {
                $cacheEnum = $this->securityService->app->enums->cacheStore($semaphoreConfig->ref);
                $cacheClient = $this->securityService->app->cache->getStore($cacheEnum)->store;
                if (!$cacheClient instanceof RedisClientInterface) {
                    throw new \InvalidArgumentException(
                        sprintf('Cache store "%s" is not a Redis client', $cacheEnum->name));
                }

                if (!$cacheClient instanceof LocksInterface) {
                    throw new \InvalidArgumentException(
                        sprintf('Cache store "%s" does not implement "%s"', $cacheEnum->name, LocksInterface::class));
                }

                /** @var RedisClientInterface&LocksInterface $cacheClient */
                return new SemaphoreRedis($cacheClient);
            } catch (\Throwable $t) {
                throw new WrappedException($t, "Failed to resolve Redis semaphore scope: " . $key);
            }
        }

        throw new \InvalidArgumentException("Unsupported semaphore type: " . $semaphoreConfig->provider->name);
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