<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\Contracts\Enums\SemaphoreScopeEnumInterface;
use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Registry\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Registry\Traits\RegistryKeysLowercaseTrimmed;
use Charcoal\Filesystem\Enums\PathContext;
use Charcoal\Filesystem\Enums\PathType;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\PathInfo;
use Charcoal\Filesystem\Semaphore\FileLock;
use Charcoal\Filesystem\Semaphore\SemaphoreDirectory;
use Charcoal\Semaphore\Contracts\SemaphoreProviderInterface;

/**
 * Provides management for filesystem-based semaphores using a directory-based structure.
 * Uses a factory registry pattern to handle creation and retrieval of semaphores based on scope.
 * Ensures certain traits like no cloning and no dumping are enforced.
 * @template-extends AbstractFactoryRegistry<SemaphoreProviderInterface>
 */
final class SemaphoreService extends AbstractFactoryRegistry
{
    use RegistryKeysLowercaseTrimmed;
    use ControlledSerializableTrait;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct(
        public readonly SemaphoreType $type,
        private DirectoryPath         $path
    )
    {
    }

    /**
     * @param SemaphoreScopeEnumInterface $scope
     * @return SemaphoreDirectory
     */
    public function get(SemaphoreScopeEnumInterface $scope): SemaphoreProviderInterface
    {
        return $this->getExistingOrCreate($scope->getConfigKey());
    }

    /**
     * @throws \Charcoal\Semaphore\Exceptions\SemaphoreLockException
     */
    public function lock(
        SemaphoreScopeEnumInterface $scope,
        string                      $lockId,
        ?float                      $checkInterval = null,
        int                         $maximumWait = 0
    ): FileLock
    {
        return $this->get($scope)->obtainLock($lockId, $checkInterval, max($maximumWait, 0));
    }

    /**
     * @param string $key
     * @return SemaphoreProviderInterface
     * @throws WrappedException
     */
    protected function create(string $key): SemaphoreProviderInterface
    {
        try {
            return new SemaphoreDirectory(new DirectoryPath($this->path->join($key)));
        } catch (\Exception $e) {
            throw new WrappedException($e, "Failed to resolve directory for semaphore scope: " . $key);
        }
    }

    /**
     * @return DirectoryPath[]
     */
    protected function collectSerializableData(): array
    {
        return [
            "type" => $this->type,
            "path" => $this->path
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->type = $data["type"];
        $this->path = $data["path"];
    }

    /**
     * @return array
     */
    protected function unserializeDependencies(): array
    {
        return [
            self::class,
            SemaphoreType::class,
            DirectoryPath::class,
            PathInfo::class,
            PathContext::class,
            PathType::class
        ];
    }
}