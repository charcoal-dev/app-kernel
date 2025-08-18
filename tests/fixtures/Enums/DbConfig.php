<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Enums;

use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;

/**
 * Class DbConfig
 * @package Charcoal\Tests\App\Fixtures\Enums
 * @example
 */
enum DbConfig: string implements DatabaseEnumInterface
{
    case Primary = "primary";
    case Secondary = "secondary";

    /**
     * @return string
     */
    public function getConfigKey(): string
    {
        return $this->value;
    }
}