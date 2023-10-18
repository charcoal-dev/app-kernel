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
use Charcoal\Apps\Kernel\Modules\AbstractOrmModule;
use Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject;
use Charcoal\Apps\Kernel\Modules\Objects\ObjectRegistrySource;
use Charcoal\Cache\Exception\CacheException;
use Charcoal\Database\ORM\Exception\OrmModelNotFoundException;

/**
 * Class AbstractOrmComponent
 * @package Charcoal\Apps\Kernel\Modules\Components
 * @property AbstractOrmModule $module
 */
abstract class AbstractOrmComponent extends BaseComponent
{
    public function __construct(AbstractOrmModule $module, public readonly AbstractAppTable $table)
    {
        parent::__construct($module);
    }

    /**
     * @return array|\Charcoal\Apps\Kernel\Modules\BaseModule[]
     */
    public function __serialize(): array
    {
        $data = parent::__serialize();
        $data["table"] = $this->table;
        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->table = $data["table"];
        parent::__unserialize($data);
    }

    /**
     * @param string $registryKey
     * @param \Charcoal\Apps\Kernel\Db\AbstractAppTable $table
     * @param string $tableColumn
     * @param int|string $tableValue
     * @param bool $findInCache
     * @param bool $storeInCache
     * @param int|null $cacheTtl
     * @return \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject
     * @throws \Charcoal\Apps\Kernel\Exception\AppRegistryObjectNotFound
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    protected function getObject(
        string           $registryKey,
        AbstractAppTable $table,
        string           $tableColumn,
        int|string       $tableValue,
        bool             $findInCache,
        bool             $storeInCache,
        ?int             $cacheTtl = null
    ): AbstractAppObject
    {
        $object = $this->module->objectsRegistry->get($registryKey);
        if ($object) {
            return $object;
        }

        if ($findInCache) {
            try {
                $cached = $this->module->objectsRegistry->getFromCache($registryKey);
                if ($cached) {
                    $this->module->objectsRegistry->store($object); // Store in runtime memory
                    return $cached;
                }
            } catch (CacheException $e) {
                $this->module->app->lifecycle->exception($e);
                $this->module->app->triggerError('An error occurred while retrieving object from cache', E_USER_WARNING);
            }
        }

        try {
            $object = $table->findByCol($tableColumn, $tableValue);
        } catch (OrmModelNotFoundException) {
            throw new AppRegistryObjectNotFound();
        }

        $object->metaObjectSource = ObjectRegistrySource::DB;
        $this->module->objectsRegistry->store($object);
        if ($storeInCache) {
            try {
                $this->storeInCache($object, $cacheTtl);
            } catch (CacheException $e) {
                $this->module->app->lifecycle->exception($e);
                $this->module->app->triggerError('An error occurred while storing object in cache', E_USER_WARNING);
            }
        }

        return $object;
    }

    /**
     * @param \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject $object
     * @param int|null $cacheTtl
     * @return void
     * @throws \Charcoal\Cache\Exception\CacheException
     */
    protected function storeInCache(AbstractAppObject $object, ?int $cacheTtl = null): void
    {
        $this->module->objectsRegistry->storeInCache($object, $cacheTtl);
    }

    /**
     * @param string $registryKey
     * @param \Charcoal\Apps\Kernel\Db\AbstractAppTable $table
     * @param string $tableColumn
     * @param int|string $tableValue
     * @param bool $useCache
     * @return \Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject
     * @throws \Charcoal\Cache\Exception\CacheException
     * @throws \Charcoal\Database\ORM\Exception\OrmException
     * @throws \Charcoal\Database\ORM\Exception\OrmQueryException
     */
    protected function resolveOnly(
        string           $registryKey,
        AbstractAppTable $table,
        string           $tableColumn,
        int|string       $tableValue,
        bool             $useCache,
    ): AbstractAppObject
    {
        if ($useCache) {
            return $this->module->objectsRegistry->get($registryKey) ??
                $this->module->objectsRegistry->getFromCache($registryKey) ??
                $table->findByCol($tableColumn, $tableValue);
        }

        return $this->module->objectsRegistry->get($registryKey) ??
            $table->findByCol($tableColumn, $tableValue);
    }
}
