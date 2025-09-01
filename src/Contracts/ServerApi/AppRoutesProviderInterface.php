<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\ServerApi;

use Charcoal\Http\Server\Middleware\MiddlewareRegistry;
use Charcoal\Http\Server\Routing\HttpRoutes;

/**
 * Defines the interface for providing application routes and related configurations.
 */
interface AppRoutesProviderInterface extends ServerApiContextInterface
{
    public function routes(): HttpRoutes;

    public function configPipelineCallback(MiddlewareRegistry $mw): void;
}