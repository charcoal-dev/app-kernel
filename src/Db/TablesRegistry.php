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

namespace Charcoal\Apps\Kernel\Db;

use Charcoal\OOP\DependencyInjection\AbstractInstanceRegistry;

/**
 * Class TablesRegistry
 * @package Charcoal\Apps\Kernel\Db
 */
class TablesRegistry extends AbstractInstanceRegistry
{
    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param \Charcoal\Apps\Kernel\Db\AbstractAppTable $table
     * @return void
     */
    public function register(AbstractAppTable $table): void
    {
        $this->registrySet($table->name, $table);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->instances;
    }
}
