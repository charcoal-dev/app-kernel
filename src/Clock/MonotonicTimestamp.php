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

    /**
     * @return self
     * @api
     */
    public static function now(): self
    {
        return new self();
    }

    /**
     * @return void
     * @internal
     */
    protected function __construct()
    {
        $this->timestamp = hrtime(true);
    }

    /**
     * @param MonotonicTimestamp $other
     * @return int
     */
    public function elapsed(self $other): int
    {
        return $other->timestamp - $this->timestamp;
    }
}