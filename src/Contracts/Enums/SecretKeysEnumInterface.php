<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Enums;

use Charcoal\App\Kernel\Security\Secrets\SecretEntropyRemixing;
use Charcoal\Security\Secrets\Support\SecretKeyRef;

/**
 * Interface SecretKeysEnumInterface
 * @package Charcoal\App\Kernel\Contracts\Enums
 */
interface SecretKeysEnumInterface extends \UnitEnum
{
    public function getKeyRef(): SecretKeyRef;

    public function getRemixAttributes(): ?SecretEntropyRemixing;

    public function getRef(): string;

    public function getCurrentVersion(): int;

    public function getNamespace(): ?string;

    public function getStore(): SecretsStoreEnumInterface;
}