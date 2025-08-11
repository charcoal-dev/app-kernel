<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Cipher;

use Charcoal\Base\Concerns\InstancedObjectsRegistry;
use Charcoal\Base\Concerns\RegistryKeysLowercaseTrimmed;
use Charcoal\Cipher\Cipher;

/**
 * Class CipherKeychain
 * @package Charcoal\App\Kernel
 * @template-implements InstancedObjectsRegistry<Cipher>
 */
class CipherKeychain
{
    use InstancedObjectsRegistry;
    use RegistryKeysLowercaseTrimmed;

    /**
     * @param CipherEnum $key
     * @param Cipher $cipher
     * @return void
     */
    public function set(CipherEnum $key, Cipher $cipher): void
    {
        $this->registrySet($key->value, $cipher);
    }

    /**
     * @param CipherEnum $key
     * @return Cipher|null
     */
    public function getOrNull(CipherEnum $key): ?Cipher
    {
        return $this->registryGet($key->value);
    }

    /**
     * @param CipherEnum $key
     * @return Cipher
     */
    public function get(CipherEnum $key): Cipher
    {
        $cipher = $this->getOrNull($key);
        if (!$cipher) {
            throw new \OutOfBoundsException("No such cipher stored in CipherKeychain");
        }

        return $cipher;
    }
}