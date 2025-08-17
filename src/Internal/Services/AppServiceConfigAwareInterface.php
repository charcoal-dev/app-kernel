<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Services;

use Charcoal\App\Kernel\AbstractApp;

/**
 * Interface ConfigAwareAppServiceInterface
 * @package Charcoal\App\Kernel\Internal\Services
 */
interface AppServiceConfigAwareInterface extends AppServiceInterface
{
    public function __construct(AbstractApp $app);
}