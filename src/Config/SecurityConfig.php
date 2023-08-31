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

/**
 * Class SecurityConfig
 * @package Charcoal\Apps\Kernel\Config
 */
class SecurityConfig
{
    public readonly CipherKeychain $keychain;
    public readonly int $minimumIterations;
    public readonly int $maximumIterations;

    /**
     * @param mixed $config
     * @throws \Charcoal\Apps\Kernel\Exception\AppConfigException
     */
    public function __construct(mixed $config)
    {
        if(!is_array($config)) {
            throw new AppConfigException('Invalid security configuration');
        }

        $keychain = $config["keychain"] ?? null;
        if (!is_array($keychain)) {
            throw new AppConfigException('Security config does not include cipher keychain');
        }

        $this->keychain = new CipherKeychain($keychain);

        $this->minimumIterations = intval($config["minimum_iterations"] ?? 0);
        if ($this->minimumIterations < 1 || $this->minimumIterations >= 0xffff) {
            throw new AppConfigException('Invalid value for minimum PBKDF2 iterations');
        }

        $this->maximumIterations = intval($config["maximum_iterations"] ?? 0);
        if ($this->maximumIterations < $this->minimumIterations) {
            throw new AppConfigException('Invalid value for maximum PBKDF2 iterations');
        }
    }
}
