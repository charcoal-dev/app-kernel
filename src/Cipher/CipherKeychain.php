<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Cipher;

use Charcoal\Cipher\Cipher;
use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class CipherKeychain
 * @package Charcoal\App\Kernel
 */
class CipherKeychain extends AbstractInstanceRegistry
{
    public function __construct()
    {
        parent::__construct(Cipher::class);
    }

    /**
     * @param CipherEnum|string $key
     * @param Cipher $cipher
     * @return void
     */
    public function set(CipherEnum|string $key, Cipher $cipher): void
    {
        $key = $key instanceof CipherEnum ? $key->value : $key;
        $this->registrySet($key, $cipher);
    }

    /**
     * @param CipherEnum|string $key
     * @return Cipher|null
     */
    public function getOrNull(CipherEnum|string $key): ?Cipher
    {
        $key = $key instanceof CipherEnum ? $key->value : $key;
        return $this->registryGet($key);
    }

    /**
     * @param CipherEnum|string $key
     * @return Cipher
     */
    public function get(CipherEnum|string $key): Cipher
    {
        $cipher = $this->getOrNull($key);
        if (!$cipher) {
            throw new \OutOfBoundsException("No such cipher stored in CipherKeychain");
        }

        return $cipher;
    }
}