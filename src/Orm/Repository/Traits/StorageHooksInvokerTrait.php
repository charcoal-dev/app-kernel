<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository\Traits;

use Charcoal\App\Kernel\Contracts\Orm\Entity\StorageHooksInterface;
use Charcoal\App\Kernel\Diagnostics\Diagnostics;
use Charcoal\Contracts\Storage\Enums\FetchOrigin;

/**
 * Trait StorageHooksInvokerTrait
 * @package Charcoal\App\Kernel\Orm\Repository\Traits
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
            $logEntry = $entity->onRetrieve($origin);
            if ($logEntry) {
                Diagnostics::app()->verbose($logEntry);
            }

            if ($storedInCache) {
                $logEntry = $entity->onCacheStore();
                if ($logEntry) {
                    Diagnostics::app()->verbose($logEntry);
                }
            }
        }

        return $entity;
    }
}