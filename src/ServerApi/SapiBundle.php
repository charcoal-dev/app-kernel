<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Config\Snapshot\HttpServerConfig;
use Charcoal\App\Kernel\Contracts\ServerApi\ServerApiContextInterface;
use Charcoal\App\Kernel\Contracts\ServerApi\ServerApiEnumInterface;
use Charcoal\App\Kernel\Diagnostics\Events\BuildStageEvents;
use Charcoal\App\Kernel\ServerApi\Events\ServerApiEvents;
use Charcoal\App\Kernel\ServerApi\Http\AppRouter;
use Charcoal\Contracts\Sapi\SapiType;
use Charcoal\Contracts\Sapi\ServerApiInterface;
use Charcoal\Http\Server\HttpServer;
use Charcoal\Http\Server\Middleware\MiddlewareRegistry;

/**
 * The class accepts multiple implementations of the ServerApiInterface,
 * providing a structure to handle server API functionality as a collection of routes.
 * @internal
 */
final readonly class SapiBundle
{
    private ?SapiLoaded $loaded;

    public ServerApiEvents $events;
    public int $httpCount;
    public int $cliCount;

    public function __construct(
        AbstractApp   $app,
        private array $sapiContexts,
    )
    {
        $this->events = new ServerApiEvents();
        $cliCount = 0;
        $httpCount = 0;
        foreach ($this->sapiContexts as $sapiContext) {
            if (!$sapiContext instanceof ServerApiContextInterface) {
                throw new \RuntimeException("Invalid SAPI context: " . get_debug_type($sapiContext));
            }

            if ($sapiContext instanceof AppRouter) {
                $this->createHttpServer($app, $sapiContext->sapi);
                $httpCount++;
                continue;
            }

            throw new \UnexpectedValueException("Invalid SAPI context: " . get_debug_type($sapiContext));
        }

        $this->cliCount = $cliCount;
        $this->httpCount = $httpCount;

        if ($this->httpCount > 0) {
            $app->diagnostics->buildStageStream(BuildStageEvents::HttpServersLoaded);
        }
    }

    /**
     * @return SapiLoaded
     */
    public function current(): SapiLoaded
    {
        if (!isset($this->loaded)) {
            throw new \BadMethodCallException("SAPI not loaded");
        }

        return $this->loaded;
    }

    /** @internal */
    public function load(AbstractApp $app, ?ServerApiEnumInterface $sapi): ?ServerApiInterface
    {
        if (isset($this->loaded)) {
            throw new \BadMethodCallException("SAPI already loaded");
        }

        if ($sapi) {
            $this->loaded = new SapiLoaded($sapi, match ($sapi->type()) {
                SapiType::Http => $this->createHttpServer($app, $sapi),
                default => throw new \RuntimeException("Invalid SAPI type: " . $sapi->type()->name),
            });

            $this->events->dispatch($this->loaded);
        }

        if (!isset($this->loaded)) {
            $this->loaded = null;
        }

        return $this->loaded?->sapi;
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            "events" => $this->events,
            "loaded" => null,
            "sapiContexts" => $this->sapiContexts,
            "httpCount" => $this->httpCount,
            "cliCount" => $this->cliCount,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->events = $data["events"];
        $this->sapiContexts = $data["sapiContexts"];
        $this->httpCount = $data["httpCount"];
        $this->cliCount = $data["cliCount"];
    }

    /**
     * @param AbstractApp $app
     * @param ServerApiEnumInterface $sapi
     * @return HttpServer
     */
    private function createHttpServer(AbstractApp $app, ServerApiEnumInterface $sapi): HttpServer
    {
        $appRouter = $this->sapiContexts[$sapi->name] ?? null;
        if (!$appRouter instanceof AppRouter) {
            throw new \RuntimeException("No SAPI context found for SAPI \"" . $sapi->name . "\"");
        }

        $sapiConfig = $app->config->sapi->interfaces[$sapi->name] ?? null;
        if (!isset($sapiConfig) || !$sapiConfig instanceof HttpServerConfig) {
            throw new \RuntimeException("Invalid SAPI interface config found for SAPI \"" . $sapi->name . "\"");
        }

        return new HttpServer($sapiConfig->routerConfig,
            $appRouter->routes,
            function (MiddlewareRegistry $middleware) use ($app, $sapi, $appRouter) {
                // Global-scope middleware:
                $app->setupHttpPipelinesHook($sapi, $middleware);

                // Router-specific middleware:
                $appRouter->onServerConstruct($app, $middleware);
            });
    }
}