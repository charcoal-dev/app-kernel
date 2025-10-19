<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security\Secrets;

/**
 * Class SecretEntropyRemixing
 * @package Charcoal\App\Kernel\Security\Secrets
 */
final readonly class SecretEntropyRemixing
{
    public function __construct(
        public string $message,
        public int    $iterations = 1
    )
    {
    }
}