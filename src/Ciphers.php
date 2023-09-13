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

namespace Charcoal\Apps\Kernel;

use Charcoal\Cipher\Cipher;
use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class Ciphers
 * @package Charcoal\Apps\Kernel
 */
class Ciphers extends AbstractInstanceRegistry
{
    public readonly Cipher $primary;

    /**
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     */
    public function __construct(protected readonly AbstractApp $app)
    {
        parent::__construct(null);

        // Preloaded:
        $this->primary = $this->get("primary");
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);
        $this->primary = $this->get("primary");
    }

    /**
     * @param string $key
     * @return \Charcoal\Cipher\Cipher
     */
    public function get(string $key): Cipher
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }

        $cipher = new Cipher($this->app->kernel->config->security->keychain->get($key));
        $this->registrySet($key, $cipher);
        return $cipher;
    }
}

