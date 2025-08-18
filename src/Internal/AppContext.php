<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Contracts\Domain\AppBindableInterface;

/**
 * Class AppContext
 * @package Charcoal\App\Kernel\Internal
 */
final readonly class AppContext
{
    /**
     * @param \DateTimeImmutable $timestamp
     * @param AppEnv $env
     * @param array<string, class-string<AppBindableInterface>> $domain
     */
    public function __construct(
        public AppEnv             $env,
        public array              $domain,
        public \DateTimeImmutable $timestamp,
    )
    {
    }
}