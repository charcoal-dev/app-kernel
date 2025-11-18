<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Domain;

use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\Cipher\Cipher;
use Charcoal\Security\Secrets\Support\SecretKeyRef;

/**
 * Class ModuleSecurityBindings
 * @package Charcoal\App\Kernel\Domain
 */
final readonly class ModuleSecurityBindings
{
    public function __construct(
        public ?Cipher                    $cipherAlgo,
        public ?SecretsStoreEnumInterface $secretsStore = null,
        public ?SecretKeyRef              $secretKeyRef = null,
    )
    {
        if ($this->secretsStore && !$this->secretKeyRef || $this->secretKeyRef && !$this->secretsStore) {
            throw new \LogicException("Secret key ref and store must BOTH be provided or neither");
        }
    }
}