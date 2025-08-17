<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Config;

/**
 * Class ErrorManagerConfig
 * @package Charcoal\App\Kernel\Internal\Config
 * @internal
 */
final readonly class ErrorManagerConfig
{
    /**
     * @param bool $enabled Deploy App's default error handling functions?
     * @param string|null $errorLogFile Relative path to App's root directory
     */
    public function __construct(
        public bool    $enabled,
        public ?string $errorLogFile
    )
    {
    }
}