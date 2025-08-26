<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\EntryPoint\Http\Request;

use Charcoal\App\Kernel\Enums\Http\RequestConstraint;
use Charcoal\Http\Commons\Enums\HeaderKeyValidation;
use Charcoal\Http\Commons\Enums\ParamKeyValidation;

/**
 * Represents a set of constraints for processing HTTP requests, such as maximum URI length,
 * header limitations, body size, and parameter restrictions. These constraints ensure
 * compliance with standards and system limits during request handling.
 * Upon initialization, default values are set, but specific constraints can be overridden
 * as needed during runtime.
 */
final class RequestConstraints
{
    private int $maxBodyBytes;
    private int $maxParams;
    private int $maxParamLength;
    private int $dtoMaxDepth;

    public function __construct(
        public readonly int                 $maxUriBytes = 256,
        public readonly int                 $maxHeaders = 40,
        public readonly int                 $maxHeaderLength = 256,
        public readonly HeaderKeyValidation $headerKeyValidation = HeaderKeyValidation::RFC7230,
        public readonly ParamKeyValidation  $paramKeyValidation = ParamKeyValidation::STRICT,
        int                                 $maxBodyBytes = 10240,
        int                                 $maxParams = 3, // Todo: testing only
        int                                 $maxParamLength = 256,
        int                                 $dtoMaxDepth = 3,
    )
    {
        $this->maxBodyBytes = $maxBodyBytes;
        $this->maxParams = $maxParams;
        $this->maxParamLength = $maxParamLength;
        $this->dtoMaxDepth = $dtoMaxDepth;
    }

    /**
     * Overrides a specified constraint with a new value if the provided condition is met.
     */
    public function overrideConstraint(RequestConstraint $override, mixed $value): void
    {
        if (!is_int($value) || $value < 0 || $value > 0xFFFFFFFE) {
            throw new \InvalidArgumentException("Invalid HTTP constant override value: " . $override->name);
        }

        $this->{$override->name} = $value;
    }

    /**
     * Retrieves the integer value of the specified constraint.
     */
    public function getConstraintInt(RequestConstraint $override): int
    {
        return $this->{$override->name};
    }
}