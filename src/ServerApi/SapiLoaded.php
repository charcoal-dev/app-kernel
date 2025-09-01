<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi;

use Charcoal\App\Kernel\ServerApi\Events\SapiEventsContext;
use Charcoal\Contracts\ServerApi\ServerApiEnumInterface;
use Charcoal\Contracts\ServerApi\ServerApiInterface;

/**
 * Represents the context of a loaded Server API (SAPI).
 * This class is responsible for providing access to the server API enumeration
 * and the server API instance when the event is triggered.
 */
final readonly class SapiLoaded implements SapiEventsContext
{
    public function __construct(
        public ServerApiEnumInterface $enum,
        public ServerApiInterface     $sapi,
    )
    {
    }
}