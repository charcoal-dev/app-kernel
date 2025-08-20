<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\Contracts\Enums\SemaphoreScopeEnumInterface;
use Charcoal\Base\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Base\Exceptions\WrappedException;
use Charcoal\Base\Traits\ControlledSerializableTrait;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Filesystem\Enums\PathContext;
use Charcoal\Filesystem\Enums\PathType;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Path\PathInfo;
use Charcoal\Semaphore\Filesystem\FileLock;
use Charcoal\Semaphore\Filesystem\FilesystemSemaphore;

/**
 * Provides management for filesystem-based semaphores using a directory-based structure.
 * Uses a factory registry pattern to handle creation and retrieval of semaphores based on scope.
 * Ensures certain traits like no cloning and no dumping are enforced.
 * @template-extends AbstractFactoryRegistry<FilesystemSemaphore>
 */
final class SemaphoreService extends AbstractFactoryRegistry
{
    use RegistryKeysLowercaseTrimmed;
    use ControlledSerializableTrait;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct(protected DirectoryPath $path)
    {
    }

    /**
     * @param SemaphoreScopeEnumInterface $scope
     * @return FilesystemSemaphore
     */
    public function get(SemaphoreScopeEnumInterface $scope): FilesystemSemaphore
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
     * @return FilesystemSemaphore
     * @throws WrappedException
     */
    protected function create(string $key): FilesystemSemaphore
    {
        try {
            return new FilesystemSemaphore(new DirectoryPath($this->path->absolute . "/" . $key));
        } catch (\Exception $e) {
            throw new WrappedException($e, "Failed to resolve directory for semaphore scope: " . $key);
        }
    }

    /**
     * @return DirectoryPath[]
     */
    protected function collectSerializableData(): array
    {
        return ["path" => $this->path];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->path = $data["path"];
    }

    /**
     * @return array
     */
    protected function unserializeDependencies(): array
    {
        return [
            self::class,
            DirectoryPath::class,
            PathInfo::class,
            PathContext::class,
            PathType::class
        ];
    }
}