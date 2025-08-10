<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

/**
 * Interface TableRegistryEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface TableRegistryEnumInterface
{
    public function getTableName(): string;

    public function getDatabase(): DatabaseEnumInterface;

    public function getPriority(): int;
}