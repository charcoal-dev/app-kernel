<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Entrypoints\Http;

use Charcoal\Apps\Kernel\AbstractApp;

/**
 * Class AbstractController
 * @package Charcoal\Apps\Kernel\Entrypoints\Http
 */
abstract class AbstractController extends \Charcoal\HTTP\Router\Controllers\AbstractController
{
    public readonly AbstractApp $app;
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
     * @param \Charcoal\Apps\Kernel\AbstractApp $app
     * @param string|null $remoteClientClass
     * @return void
     */
    private function bootstrapController(AbstractApp $app, ?string $remoteClientClass = RemoteClient::class): void
    {
        $this->app = $app;
        $this->userClient = new $remoteClientClass($this->request);
    }

    /**
     * @param \Exception $e
     * @return array
     */
    protected function getExceptionTrace(\Exception $e): array
    {
        return array_map(function (array $trace) {
            unset($trace["args"]);
            return $trace;
        }, $e->getTrace());
    }
}
