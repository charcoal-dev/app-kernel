<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Security;

use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\Filesystem\Path\DirectoryPath;

/**
 * Class SecretsConfig
 * @package Charcoal\App\Kernel\Internal\Security
 */
final readonly class SecretsConfig
{
    public function __construct(
        public SecretsStoreEnumInterface $provider,
        public string|DirectoryPath      $ref,
    )
    {
    }
}