<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\EntryPoint;

use Charcoal\App\Kernel\Contracts\Enums\SapiEnumInterface;
use Charcoal\Http\Server\Middleware\MiddlewareRegistry;
use Charcoal\Http\Server\Routing\AppRoutes;

/**
 * Defines the interface for providing application routes and related configurations.
 */
interface AppRoutesProviderInterface
{
    public function sapi(): SapiEnumInterface;

    public function routes(): AppRoutes;

    public function configPipelineCallback(MiddlewareRegistry $mw): void;
}