<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security\Secrets;

use Charcoal\App\Kernel\Contracts\Enums\SecretsStoreEnumInterface;
use Charcoal\Security\Secrets\SecretsKms;

/**
 * The secret reference includes a store identifier, the reference string,
 * and an optional namespace. Validation is performed on the reference
 * and namespace formats to ensure they adhere to predefined patterns.
 */
final readonly class SecretRef
{
    public function __construct(
        public SecretsStoreEnumInterface $store,
        public string                    $ref,
        public int                       $version,
        public ?string                   $namespace = null,
    )
    {
        if (!$this->ref || !preg_match(SecretsKms::REF_REGEXP, $this->ref)) {
            throw new \InvalidArgumentException("Invalid secret reference");
        }

        if ($this->version < 1 || $this->version > 65535) {
            throw new \InvalidArgumentException("Invalid secret version");
        }

        if ($this->namespace) {
            if (!preg_match(SecretsKms::NAMESPACE_REGEXP, $this->namespace)) {
                throw new \InvalidArgumentException("Invalid namespace for secret reference: " . $this->ref);
            }
        }
    }
}