<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

use Charcoal\App\Kernel\Secrets\Enums\SecretsStore;

interface SecretsNamespaceEnumInterface extends \UnitEnum
{
    public function getStore(): SecretsStore;
}