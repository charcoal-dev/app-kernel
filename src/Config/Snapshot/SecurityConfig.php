<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Filesystem\Enums\PathType;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Semaphore\Filesystem\FilesystemSemaphore;

/**
 * Represents a configuration object for managing security-related settings.
 */
final readonly class SecurityConfig implements ConfigSnapshotInterface
{
    use NoDumpTrait;

    public function __construct(
        public ?DirectoryPath $semaphoreDirectory
    )
    {
        if ($this->semaphoreDirectory) {
            if ($this->semaphoreDirectory->type === PathType::Missing) {
                throw new \DomainException("Semaphore directory does not exist");
            }

            try {
                new FilesystemSemaphore($this->semaphoreDirectory);
            } catch (\Exception) {
                throw new \DomainException("Semaphore directory returned permission error");
            }
        }
    }
}