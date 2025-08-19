<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Config;

/**
 * Interface ConfigEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface ConfigEnumInterface extends \UnitEnum
{
    public function getConfigKey(): string;
}