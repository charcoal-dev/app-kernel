<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Clock;

/**
 * Class MonotonicTimestamp
 * @package Charcoal\App\Kernel\Clock
 */
final readonly class MonotonicTimestamp
{
    public int $timestamp;

    public function __construct()
    {
        $this->timestamp = hrtime(true);
    }
}