<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Enums\AppEnv;

/**
 * Represents the global application context containing environment configuration,
 * initialization timestamp, root path information, and domain manifest.
 */
final readonly class AppContext
{
    public function __construct(
        public AppEnv             $env,
        public \DateTimeImmutable $timestamp,
        public array              $domain,
    )
    {
    }
}