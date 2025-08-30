<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\SecurityConfig;
use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use Charcoal\Filesystem\Exceptions\FilesystemException;
use Charcoal\Filesystem\Path\DirectoryPath;

/**
 * Represents a builder class for constructing security configuration instances.
 * Implements the ConfigBuilderInterface for standardized configuration building.
 */
final class SecurityConfigBuilder implements ConfigBuilderInterface
{
    protected ?DirectoryPath $semaphorePrivate = null;
    protected ?DirectoryPath $semaphoreShared = null;

    public function __construct(protected DirectoryPath $root)
    {
    }

    /**
     * @api
     */
    public function setSemaphoreDirectory(SemaphoreType $type, string $dir): self
    {
        $prop = match ($type) {
            SemaphoreType::Filesystem_Private => "semaphorePrivate",
            SemaphoreType::Filesystem_Shared => "semaphoreShared",
        };

        try {
            $this->$prop = $this->root->join($dir)->isDirectory();
        } catch (FilesystemException $e) {
            throw new \DomainException("Failed to load semaphore directory " . $type->name, previous: $e);
        }

        return $this;
    }

    /**
     * @return SecurityConfig
     */
    public function build(): SecurityConfig
    {
        return new SecurityConfig($this->semaphorePrivate, $this->semaphoreShared);
    }
}