<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Domain;

use Charcoal\App\Kernel\Contracts\Enums\SecretKeysEnumInterface;
use Charcoal\Cipher\Cipher;

/**
 * Class ModuleSecurityBindings
 * @package Charcoal\App\Kernel\Domain
 */
final readonly class ModuleSecurityBindings
{
    public function __construct(
        public ?Cipher                  $cipherAlgo,
        public ?SecretKeysEnumInterface $secretKeyEnum,
    )
    {
    }
}