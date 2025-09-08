<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Domain;

use Charcoal\App\Kernel\Domain\AbstractModule;

/**
 * Interface ModuleBindableInterface
 * Represents a contract for classes that can be bound to a module and initialized through a bootstrap process.
 */
interface ModuleBindableInterface
{
    public function bootstrap(AbstractModule $module): void;
}