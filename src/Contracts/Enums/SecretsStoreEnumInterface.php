<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

use Charcoal\App\Kernel\Enums\SecretsStoreType;
use Charcoal\App\Kernel\Internal\Config\ConfigEnumInterface;

/**
 * Interface SecretsStoreEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface SecretsStoreEnumInterface extends ConfigEnumInterface
{
    public function getStoreType(): SecretsStoreType;
}