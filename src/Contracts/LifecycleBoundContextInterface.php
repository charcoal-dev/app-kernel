<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

/**
 * Interface LifecycleBoundContextInterface
 * @package Charcoal\App\Kernel\Contracts
 */
interface LifecycleBoundContextInterface
{
    public function entryFromLifecycle(string $level, string $entry, int|string|bool|null $value = null): void;

    public function exceptionFromLifecycle(\Throwable $t): void;
}