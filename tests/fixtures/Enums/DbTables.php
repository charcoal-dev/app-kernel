<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Enums;

use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Contracts\Enums\TableRegistryEnumInterface;

/**
 * Class DbTables
 * @package Charcoal\Tests\App\Fixtures\Enums
 * @example
 */
enum DbTables: string implements TableRegistryEnumInterface
{
    case Example = "example";
    case Test = "test";

    public function getTableName(): string
    {
        return $this->value;
    }

    public function getDatabase(): DatabaseEnumInterface
    {
        return match ($this) {
            self::Test => DbConfig::Secondary,
            default => DbConfig::Primary,
        };
    }

    public function getPriority(): int
    {
        return 0;
    }
}