<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

use Charcoal\App\Kernel\Entity\EntitySource;

/**
 * Trait StorageHooksInvokerTrait
 * @package Charcoal\App\Kernel\Contracts
 */
trait StorageHooksInvokerTrait
{
    /**
     * @param object $entity
     * @param EntitySource $source
     * @param bool $storedInCache
     * @return object
     */
    protected function invokeStorageHooks(
        object       $entity,
        EntitySource $source,
        bool         $storedInCache = false
    ): object
    {
        // Invoke StorageHooksInterface
        if ($entity instanceof StorageHooksInterface) {
            $lifecycleEntry = $entity->onRetrieve($source);
            if ($lifecycleEntry) {
                $this->module->app->lifecycle->log($lifecycleEntry, $source->value, true);
            }

            if ($storedInCache) {
                $lifecycleEntry = $entity->onCacheStore();
                if ($lifecycleEntry) {
                    $this->module->app->lifecycle->log($lifecycleEntry, null, true);
                }
            }
        }

        return $entity;
    }
}