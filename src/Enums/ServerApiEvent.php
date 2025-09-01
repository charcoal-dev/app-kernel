<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums;

use Charcoal\App\Kernel\ServerApi\SapiLoaded;

/**
 * Enum cases correspond to specific events that can be emitted by the server API.
 * Each case returns its corresponding event context class.
 */
enum ServerApiEvent: int
{
    case onLoaded = 1;

    /**
     * @return class-string
     */
    public function getEventContext(): string
    {
        return match ($this) {
            self::onLoaded => SapiLoaded::class,
        };
    }
}