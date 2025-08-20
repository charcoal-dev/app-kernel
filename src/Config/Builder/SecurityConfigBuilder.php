<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\SecurityConfig;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use Charcoal\Filesystem\Path\DirectoryPath;

/**
 * Represents a builder class for constructing security configuration instances.
 * Implements the ConfigBuilderInterface for standardized configuration building.
 */
final class SecurityConfigBuilder implements ConfigBuilderInterface
{
    protected ?DirectoryPath $semaphoreDirectory = null;

    public function __construct(protected DirectoryPath $root)
    {
    }

    /**
     * @api
     */
    public function setSemaphoreDirectory(string $dir): self
    {
        try {
            $this->semaphoreDirectory = new DirectoryPath($this->root->absolute . "/" . ltrim($dir, "."));
        } catch (\Throwable $e) {
            throw new \DomainException("Failed to load semaphore directory", previous: $e);
        }

        return $this;
    }

    /**
     * @return SecurityConfig
     */
    public function build(): SecurityConfig
    {
        return new SecurityConfig($this->semaphoreDirectory);
    }
}