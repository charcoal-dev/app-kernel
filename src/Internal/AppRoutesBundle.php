<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal;

use Charcoal\App\Kernel\Contracts\EntryPoint\AppRoutesProviderInterface;
use Charcoal\Base\Vectors\AbstractVector;

/**
 * Represents a collection of AppRoutes objects.
 * This class is a bundle that extends the AbstractVector class and
 * provides functionality to manage multiple AppRoutes objects.
 * @extends AbstractVector<AppRoutesProviderInterface>
 * @method AppRoutesProviderInterface[] getIterator()
 * @method AppRoutesProviderInterface[] getArray()
 */
final class AppRoutesBundle extends AbstractVector
{
    public function __construct(AppRoutesProviderInterface ...$routes)
    {
        parent::__construct($routes);
    }
}