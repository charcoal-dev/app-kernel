<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Context;

/**
 * Class AppContext
 * @package Charcoal\App\Kernel\Build
 */
final readonly class AppContext
{
    public function __construct(
        public \DateTimeImmutable       $timestamp,
        public array                    $moduleClasses,
        public array                    $moduleProperties,
    )
    {
    }
}