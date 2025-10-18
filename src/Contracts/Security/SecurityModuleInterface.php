<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\Security;

use Charcoal\App\Kernel\Security\SecurityService;

/**
 * Interface SecurityModuleInterface
 * @package Charcoal\App\Kernel\Contracts\Security
 */
interface SecurityModuleInterface
{
    public function bootstrap(SecurityService $securityService): void;
}