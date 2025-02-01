<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Interfaces\Http;

use Charcoal\App\Kernel\AppBuild;

/**
 * Class AbstractEndpoint
 * @package Charcoal\App\Kernel\Interfaces\Http
 */
abstract class AbstractEndpoint extends \Charcoal\HTTP\Router\Controllers\AbstractController
{
    public readonly AppBuild $app;
    public readonly RemoteClient $userClient;

    /**
     * @param array $args
     * @return void
     */
    protected function onConstruct(array $args): void
    {
        $this->bootstrapController($args[0], $args[1] ?? RemoteClient::class);
        $this->dispatchEntrypoint();
    }

    /**
     * Separate method for strict typing
     * @param AppBuild $app
     * @param string $remoteClientClass
     * @return void
     */
    private function bootstrapController(AppBuild $app, string $remoteClientClass = RemoteClient::class): void
    {
        $this->app = $app;
        $this->userClient = new $remoteClientClass($this->request);
    }

    /**
     * Separate overridable method
     * @return void
     */
    protected function dispatchEntrypoint(): void
    {
        try {
            $entrypoint = $this->resolveEntrypoint(); // Resolves callable method or THROW,
            $this->beforeEntrypointCallback(); // Entrypoint method was resolved
            call_user_func($entrypoint);
            $this->afterEntrypointCallback();
        } catch (\Throwable $t) {
            $this->handleException($t);
        }
    }

    /**
     * @return callable
     */
    abstract protected function resolveEntrypoint(): callable;

    /**
     * Objective of this method must be to parse caught Throwable object into Response
     * @param \Throwable $t
     * @return void
     */
    abstract protected function handleException(\Throwable $t): void;

    /**
     * Hook called right before entrypoint method is invoked,
     * This will not be invoked if resolveEntrypointMethod() throws
     * @return void
     */
    abstract protected function beforeEntrypointCallback(): void;

    /**
     * Hook called right after entrypoint method is invoked
     *  This will not be invoked if resolveEntrypointMethod() throws
     * @return void
     */
    protected function afterEntrypointCallback(): void
    {
    }
}