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

namespace Charcoal\Apps\Kernel\Modules;

/**
 * Class AbstractComponentsModule
 * @package Charcoal\Apps\Kernel\Modules
 */
abstract class AbstractOrmModule extends BaseModule
{
    public readonly TablesRegistry $tables;
    public readonly ObjectsRegistry $objectsRegistry;

    public function __construct()
    {
        $this->objectsRegistry = new ObjectsRegistry($this);
        $this->tables = new TablesRegistry();
    }

    /**
     * @return \Charcoal\Apps\Kernel\Modules\TablesRegistry[]
     */
    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["tables"] = $this->tables;
        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->tables = $data["tables"];
        $this->objectsRegistry = new ObjectsRegistry($this);
        parent::__unserialize($data);
    }
}
