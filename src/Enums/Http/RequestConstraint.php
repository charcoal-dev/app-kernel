<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Enums\Http;

/**
 * Represents a set of constraints that can be overridden with specific options.
 */
enum RequestConstraint
{
    case maxBodyBytes;
    case maxParams;
    case maxParamLength;
    case dtoMaxDepth;
}