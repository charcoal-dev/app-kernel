<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\StorageHooks;

use Charcoal\App\Kernel\Contracts\Orm\Entity\StorageHooksInterface;
use Charcoal\Base\Enums\FetchOrigin;

/**
 * Trait StorageHooksInvokerTrait
 * @package Charcoal\App\Kernel\Contracts\StorageHooks
 */
trait StorageHooksInvokerTrait
{
    /**
     * @param object $entity
     * @param FetchOrigin $origin
     * @param bool $storedInCache
     * @return object
     */
    protected function invokeStorageHooks(
        object      $entity,
        FetchOrigin $origin,
        bool        $storedInCache = false
    ): object
    {
        // Invoke StorageHooksInterface
        if ($entity instanceof StorageHooksInterface) {
            $lifecycleEntry = $entity->onRetrieve($origin);
            if ($lifecycleEntry) {
                $this->module->app->lifecycle->log($lifecycleEntry, $origin->value, true);
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