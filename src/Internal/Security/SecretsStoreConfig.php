<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Security;

use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\Filesystem\Path\DirectoryPath;
use Charcoal\Security\Secrets\Contracts\SecretsProviderInterface;
use Charcoal\Security\Secrets\Enums\KeySize;

/**
 * Class SecretsConfig
 * @package Charcoal\App\Kernel\Internal\Security
 * @internal
 */
final readonly class SecretsStoreConfig implements SecretsProviderInterface
{
    public function __construct(
        public SecretsStoreEnumInterface $provider,
        public DirectoryPath             $ref,
        public KeySize                   $keySize,
    )
    {
    }

    public function getId(): string
    {
        return $this->provider->name;
    }

    public function resolvePath(): string|DirectoryPath
    {
        return $this->ref;
    }

    public function getKeySize(): KeySize
    {
        return $this->keySize;
    }
}