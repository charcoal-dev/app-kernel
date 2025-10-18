<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Config;

/**
 * An interface that extends the base UnitEnum and provides functionality
 * for retrieving a configuration key associated with the implementing enum.
 */
interface ConfigEnumInterface extends \UnitEnum
{
    public function getConfigKey(): string;

    public static function find(string $key): self;
}