<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entrypoints\Http;

use Charcoal\App\Kernel\AppKernel;

abstract class AbstractController extends \Charcoal\HTTP\Router\Controllers\AbstractController
{
    public readonly AppKernel $app;
    public readonly RemoteClient $userClient;

    /**
     * @param array $args
     * @return void
     */
    protected function onConstruct(array $args): void
    {
        $this->bootstrapController($args[0], $args[1] ?? null);
    }

    /**
     * @param AppKernel $app
     * @param string|null $remoteClientClass
     * @return void
     */
    private function bootstrapController(AppKernel $app, ?string $remoteClientClass = RemoteClient::class): void
    {
        $this->app = $app;
        $this->userClient = new $remoteClientClass($this->request);
    }
}