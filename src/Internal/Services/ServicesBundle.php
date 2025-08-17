<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Services;

use Charcoal\Base\Vectors\AbstractVector;

/**
 * Class ServicesBundle
 * @package Charcoal\App\Kernel\Internal\Services
 * @extends AbstractVector<AppServiceInterface>
 */
final class ServicesBundle extends AbstractVector
{
    public function __construct(AppServiceInterface ...$services)
    {
        parent::__construct($services);
    }
}