<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Interfaces\Http;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\Base\Support\Helpers\ObjectHelper;
use Charcoal\Http\Router\Controller\AbstractController;

/**
 * Class AbstractRouteController
 * @package Charcoal\App\Kernel\Interfaces\Http
 */
abstract class AbstractRouteController extends AbstractController
{
    public readonly AbstractApp $app;
    public readonly RemoteClient $userClient;

    /**
     * @param array $args
     * @return void
     */
    final protected function resolveEntrypoint(array $args): void
    {
        if (!$args[0] instanceof AbstractApp) {
            throw new \InvalidArgumentException("First argument must be an instance of AppBuild");
        }

        $remoteClientClass = $args[1] ?? RemoteClient::class;
        $this->userClient = new $remoteClientClass($this->request);
        if (!$this->app->errors->hasHandlersSet()) {
            throw new \LogicException(ObjectHelper::baseClassName($this->app::class) .
                " error handlers not set; Cannot proceed to HTTP interface");
        }

        try {
            $entrypoint = $this->delegateResolveEntrypoint();
            $this->beforeEntrypointCallback();
            call_user_func($entrypoint);
            $this->afterEntrypointCallback();
        } catch (\Throwable $t) {
            $this->handleException($t);
        }
    }

    /**
     * @return callable
     */
    abstract protected function delegateResolveEntrypoint(): callable;

    /**
     * @param \Throwable $t
     * @return void
     */
    abstract protected function handleException(\Throwable $t): void;

    /**
     * @return void
     */
    abstract protected function beforeEntrypointCallback(): void;

    /**
     * @return void
     */
    protected function afterEntrypointCallback(): void
    {
    }
}