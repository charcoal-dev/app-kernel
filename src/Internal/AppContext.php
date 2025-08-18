<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Enums\AppEnv;
use Charcoal\Filesystem\Path\PathInfo;

/**
 * Class AppContext
 * @package Charcoal\App\Kernel\Internal
 */
final readonly class AppContext
{
    /**
     * @param AppEnv $env
     * @param \DateTimeImmutable $timestamp
     * @param PathInfo $root
     * @param array $domain
     */
    public function __construct(
        public AppEnv             $env,
        public \DateTimeImmutable $timestamp,
        public PathInfo           $root,
        public array              $domain,
    )
    {
    }
}