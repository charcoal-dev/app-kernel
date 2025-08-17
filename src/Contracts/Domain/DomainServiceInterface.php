<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Domain;

use Charcoal\App\Kernel\AbstractApp;

/**
 * Interface DomainServiceInterface
 * @package Charcoal\App\Kernel\Contracts\Domain
 */
interface DomainServiceInterface
{
    public function bootstrap(AbstractApp $app): void;
}