<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Filesystem\Enums\PathType;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Filesystem\Semaphore\SemaphoreDirectory;

/**
 * Represents a configuration object for managing security-related settings.
 */
final readonly class SecurityConfig implements ConfigSnapshotInterface
{
    use NoDumpTrait;

    public function __construct(
        public ?DirectoryPath $semaphorePrivate,
        public ?DirectoryPath $semaphoreShared,
    )
    {
        foreach (SemaphoreType::cases() as $semaphoreProvider) {
            $prop = match ($semaphoreProvider) {
                SemaphoreType::Filesystem_Private => "semaphorePrivate",
                SemaphoreType::Filesystem_Shared => "semaphoreShared",
            };

            if ($this->$prop) {
                if ($this->$prop->type === PathType::Missing) {
                    throw new \DomainException(sprintf("Semaphore[%s] directory does not exist",
                        $semaphoreProvider->name));
                }

                try {
                    new SemaphoreDirectory($this->$prop);
                } catch (\Exception $e) {
                    throw new \DomainException(sprintf("Semaphore[%s] directory has permission error",
                        $semaphoreProvider->name), previous: $e);
                }
            }
        }
    }
}