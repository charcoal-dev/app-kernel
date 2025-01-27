<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Container;

use Charcoal\App\Kernel\AppKernel;

/**
 * Class AppAwareContainer
 * @package Charcoal\App\Kernel\Container
 */
abstract class AppAwareContainer extends AppAware
{
    protected const array APP_AWARE_CHILDREN = [];
    private array $appAwareChildren;

    protected function __construct(?\Closure $declareChildren, array $closureArgs = [])
    {
        // Initialize children vector
        $this->appAwareChildren = static::APP_AWARE_CHILDREN;

        // Callback
        if ($declareChildren) {
            call_user_func_array($declareChildren, $closureArgs);

            // Determine children AppAwareComponent instances
            $reflect = new \ReflectionClass($this);
            foreach ($reflect->getProperties() as $property) {
                if ($property->getValue($this) instanceof AppAware) {
                    $this->appAwareChildren[] = $property->getName();
                }
            }
        }
    }

    public function bootstrap(AppKernel $app): void
    {
        parent::bootstrap($app);
        foreach ($this->appAwareChildren as $child) {
            ($this->$child ?? null)?->bootstrap($app);
        }
    }

    protected function collectSerializableData(): array
    {
        return ["appAwareChildren" => $this->appAwareChildren];
    }


    protected function onUnserialize(array $data): void
    {
        $this->appAwareChildren = $data["appAwareChildren"];
    }
}