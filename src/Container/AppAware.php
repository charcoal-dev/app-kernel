<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppKernel;

/**
 * Class AppAware
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAware
{
    public readonly AppKernel $app;

    public function bootstrap(AppKernel $app): void
    {
        $this->app = $app;
    }

    abstract protected function collectSerializableData(): array;

    abstract protected function onUnserialize(array $data): void;

    final public function __serialize(): array
    {
        return $this->collectSerializableData();
    }

    final public function __unserialize(array $data): void
    {
        $this->onUnserialize($data);
    }
}