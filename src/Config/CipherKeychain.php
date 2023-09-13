<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Config;

use Charcoal\Apps\Kernel\Exception\AppConfigException;
use Charcoal\Buffers\Frames\Bytes32;
use Charcoal\OOP\Traits\NoDumpTrait;

/**
 * Class CipherKeychain
 * @package Charcoal\Apps\Kernel\Config
 */
class CipherKeychain
{
    public const DEFAULT_INSECURE_VALUE = "enter some random words or 32 bytes PRNG entropy (hex-encoded) here";

    /** @var array */
    private array $keychain = [];

    use NoDumpTrait;

    /**
     * @param array $keys
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     */
    public function __construct(array $keys)
    {
        $defaultEntropy = hash("sha256", self::DEFAULT_INSECURE_VALUE, true);

        foreach ($keys as $label => $entropy) {
            if (preg_match('/^\w{2,16}$/', $label)) {
                if (is_string($entropy) && $entropy) {
                    $entropy = preg_match('/^(0x)?[a-f0-9]{64}$/i', $entropy) ?
                        Bytes32::fromBase16($entropy) : new Bytes32(hash("sha256", $entropy, true));
                    if ($entropy->equals($defaultEntropy)) {
                        throw new AppConfigException(sprintf('Insecure entropy for cipher key "%s"', $label));
                    }

                    $this->keychain[$label] = $entropy;
                }
            }
        }
    }

    /**
     * @param string $key
     * @return \Charcoal\Buffers\Frames\Bytes32
     */
    public function get(string $key): Bytes32
    {
        if (!isset($this->keychain[$key])) {
            throw new \OutOfRangeException(sprintf('Cipher key for "%s" not configured in keychain', $key));
        }

        return $this->keychain[$key];
    }
}