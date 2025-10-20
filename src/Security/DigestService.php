<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\Enums\DigestAlgo;
use Charcoal\Buffers\Abstracts\FixedLengthImmutableBuffer;
use Charcoal\Contracts\Buffers\ReadableBufferInterface;
use Charcoal\Contracts\Security\Secrets\SecretKeyInterface;

/**
 * Class DigestService
 * @package Charcoal\App\Kernel\Security
 */
final readonly class DigestService
{
    /**
     * @param DigestAlgo $algo
     * @param string $data
     * @param int $iterations
     * @return string
     */
    public function hash(DigestAlgo $algo, string $data, int $iterations = 1): string
    {
        if ($iterations < 1) {
            throw new \InvalidArgumentException("Invalid number of iterations: " . $iterations);
        } elseif (!$data) {
            throw new \InvalidArgumentException("Invalid data to hash");
        }

        $hashed = $data;
        for ($i = 0; $i < $iterations; $i++) {
            $hashed = hash($algo->value, $hashed, binary: true);
        }

        return $hashed;
    }

    /**
     * @param DigestAlgo $algo
     * @param ReadableBufferInterface|SecretKeyInterface $secret
     * @param string $data
     * @param int $iterations
     * @return string
     */
    public function hmac(
        DigestAlgo                                 $algo,
        ReadableBufferInterface|SecretKeyInterface $secret,
        string                                     $data,
        int                                        $iterations = 1
    ): string
    {
        if ($iterations < 1) {
            throw new \InvalidArgumentException("Invalid number of iterations: " . $iterations);
        } elseif (!$data) {
            throw new \InvalidArgumentException("Invalid data to hash");
        }

        $entropy = $this->getSecretEntropy($secret);
        $hashed = $data;
        for ($i = 0; $i < $iterations; $i++) {
            $hashed = hash_hmac($algo->value, $hashed, $entropy, binary: true);
        }

        return $hashed;
    }

    /**
     * @param FixedLengthImmutableBuffer|SecretKeyInterface $secretKey
     * @return string
     */
    private function getSecretEntropy(FixedLengthImmutableBuffer|SecretKeyInterface $secretKey): string
    {
        // Secret Keys
        $entropy = null;
        if ($secretKey instanceof SecretKeyInterface) {
            $secretKey->useSecretEntropy(function (string $secretEntropy) use (&$entropy) {
                $entropy = $secretEntropy;
            });

            return $entropy;
        }

        // Readable Fixed-length buffer frames
        return $secretKey->bytes();
    }
}