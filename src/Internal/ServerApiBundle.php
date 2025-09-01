<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\Base\Vectors\AbstractVector;
use Charcoal\Contracts\ServerApi\ServerApiInterface;

/**
 * Represents a collection of AppRoutes objects.
 * This class is a bundle that extends the AbstractVector class and
 * provides functionality to manage multiple AppRoutes objects.
 * @extends AbstractVector<ServerApiInterface>
 * @method ServerApiInterface[] getIterator()
 * @method ServerApiInterface[] getArray()
 */
final class ServerApiBundle extends AbstractVector
{
    public function __construct(ServerApiInterface ...$routes)
    {
        parent::__construct($routes);
    }
}