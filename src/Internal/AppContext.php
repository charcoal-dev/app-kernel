<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Clock\MonotonicTimestamp;
use Charcoal\App\Kernel\Contracts\Domain\AppBindableInterface;
use Charcoal\App\Kernel\Enums\AppEnv;

/**
 * Class AppContext
 * @package Charcoal\App\Kernel\Internal
 */
final readonly class AppContext
{
    /**
     * @param MonotonicTimestamp $startTime
     * @param AppEnv $env
     * @param array<string, class-string<AppBindableInterface>> $domain
     */
    public function __construct(
        public AppEnv             $env,
        public MonotonicTimestamp $startTime,
        public array              $domain,
    )
    {
    }
}