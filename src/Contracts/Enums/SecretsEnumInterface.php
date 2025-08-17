<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

/**
 * Interface SecretsEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface SecretsEnumInterface extends \UnitEnum
{
    public function getNamespace(): string;

    public function getReferenceId(): string;
}