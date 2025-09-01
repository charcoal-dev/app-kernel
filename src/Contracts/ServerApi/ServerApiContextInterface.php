<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\ServerApi;

use Charcoal\Contracts\ServerApi\ServerApiEnumInterface;

/**
 * An interface representing the context for the server API.
 * Provides a contract for server-related operations and configurations.
 */
interface ServerApiContextInterface
{
    public function sapi(): ServerApiEnumInterface;
}