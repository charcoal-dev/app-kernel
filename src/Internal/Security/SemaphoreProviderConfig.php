<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Security;

use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
use Charcoal\Filesystem\Path\DirectoryPath;

/**
 * Class SemaphoreConfig
 * @package Charcoal\App\Kernel\Internal\Security
 * @internal
 */
final readonly class SemaphoreProviderConfig
{
    public function __construct(
        public SemaphoreProviderEnumInterface $provider,
        public DirectoryPath|string           $ref,
    )
    {
    }
}