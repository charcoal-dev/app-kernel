<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Http;

use Charcoal\App\Kernel\Contracts\ServerApi\ServerApiContextInterface;
use Charcoal\Contracts\ServerApi\ServerApiEnumInterface;
use Charcoal\Http\Server\Middleware\MiddlewareRegistry;
use Charcoal\Http\Server\Routing\HttpRoutes;
use Charcoal\Http\Server\Routing\Snapshot\RoutingSnapshot;

/**
 * Represents a readonly application router that handles server API enumeration,
 * HTTP routes, and middleware registration.
 */
abstract readonly class AppRouter implements ServerApiContextInterface
{
    public RoutingSnapshot $routes;

    final public function __construct(public ServerApiEnumInterface $sapi)
    {
        $this->routes = $this->declareRoutes()->snapshot();
    }

    abstract protected function middleware(): MiddlewareRegistry;

    abstract protected function declareRoutes(): HttpRoutes;

    abstract public function onServerConstruct(MiddlewareRegistry $mw): void;

    /**
     * @return ServerApiEnumInterface
     */
    final public function sapi(): ServerApiEnumInterface
    {
        return $this->sapi;
    }
}