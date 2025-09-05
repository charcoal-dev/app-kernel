<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

use Charcoal\Database\Enums\DbDriver;

/**
 * Interface TableRegistryEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface TableRegistryEnumInterface
{
    public function getTableName(): string;

    public function getDatabase(): DatabaseEnumInterface;

    public function getDriver(): DbDriver;

    public function getPriority(): int;
}