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
    }
}
