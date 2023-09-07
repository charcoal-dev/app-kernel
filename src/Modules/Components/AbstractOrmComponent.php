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

namespace Charcoal\Apps\Kernel\Modules\Components;

use Charcoal\Apps\Kernel\Db\AbstractAppTable;
use Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound;
use Charcoal\Apps\Kernel\Modules\AbstractModule;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Database\ORM\Exception\OrmModelNotFoundException;

/**
 * Class AbstractOrmComponent
 * @package Charcoal\Apps\Kernel\Modules\Components
 */
abstract class AbstractOrmComponent extends AbstractComponent
{
    /**
     * @param \Charcoal\Apps\Kernel\Modules\AbstractModule $module
     * @param \Charcoal\Apps\Kernel\Db\AbstractAppTable $table
     */
    public function __construct(
        AbstractModule         $module,
        public readonly AbstractAppTable $table
    )
    {
        parent::__construct($module);
    }

    /**
     * @param string $registryKey
     * @param string $tableColumn
     * @param int|string $tableValue
     * @param bool $useCache
     * @return \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     */
    protected function getObject(
        string     $registryKey,
        string     $tableColumn,
        int|string $tableValue,
        bool       $useCache
    ): AbstractAppObject
    {
        $object = $this->module->objectsRegistry->get($registryKey);
        if ($object) {
            return $object;
        }

        if ($useCache) {
            try {
                return $this->module->objectsRegistry->getFromCache($registryKey);
            } catch (CacheException $e) {
                // If cache is not available, generate warning but keep execution
                $this->module->app->triggerError($e, E_USER_WARNING);
            }
        }

        try {
            $object = $this->table->findByCol($tableColumn, $tableValue);
        } catch (OrmModelNotFoundException) {
            throw new AppRegistryObjectNotFound();
        }

        $this->module->objectsRegistry->store($object);
        if ($useCache) {
            try {
                $this->module->objectsRegistry->storeInCache($object);
            } catch (CacheException $e) {
                $this->module->app->triggerError($e, E_USER_WARNING);
            }
        }

        return $object;
    }

    /**
     * @param string $registryKey
     * @param string $tableColumn
     * @param int|string $tableValue
     * @param bool $useCache
     * @return \Charcoal\Apps\Kernel\Modules\Components\AbstractAppObject
     * @throws \Charcoal\Cache\Exception\CacheException
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     */
    protected function resolveOnly(
        string     $registryKey,
        string     $tableColumn,
        int|string $tableValue,
        bool       $useCache,
    ): AbstractAppObject
    {
        if ($useCache) {
            return $this->module->objectsRegistry->get($registryKey) ??
                $this->module->objectsRegistry->getFromCache($registryKey) ??
                $this->table->findByCol($tableColumn, $tableValue);
        }

        return $this->module->objectsRegistry->get($registryKey) ??
            $this->table->findByCol($tableColumn, $tableValue);
    }
}
