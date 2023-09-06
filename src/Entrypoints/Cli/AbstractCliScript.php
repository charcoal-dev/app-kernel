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

namespace Charcoal\Apps\Kernel\Entrypoints\Cli;

use Charcoal\OOP\OOP;

/**
 * Class AbstractCliScript
 * @package Charcoal\Apps\Kernel\Entrypoints\Cli
 * @property \Charcoal\Apps\Kernel\Entrypoints\Cli\CLI $cli
 */
abstract class AbstractCliScript extends \Charcoal\CLI\AbstractCliScript
{
    public readonly CliScriptOptions $options;
    public readonly string $scriptClassname;

    /**
     * @param \Charcoal\Apps\Kernel\Entrypoints\Cli\CLI $cli
     */
    public function __construct(CLI $cli)
    {
        parent::__construct($cli);
        $this->options = new CliScriptOptions();
        $this->scriptClassname = OOP::baseClassName(static::class);
    }
}
