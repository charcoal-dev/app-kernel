<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Config\Snapshot\SecurityConfig;
use Charcoal\App\Kernel\Contracts\Domain\AppBootstrappableInterface;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;

/**
 * Represents a security service implementation within the application.
 * This final, readonly class ensures that its instances are immutable and cannot be cloned or dumped.
 */
readonly class SecurityService implements AppServiceInterface, AppBootstrappableInterface
{
    use ControlledSerializableTrait;
    use NotCloneableTrait;
    use NoDumpTrait;

    public AbstractApp $app;
    public SecurityConfig $config;
    public SemaphoreService $semaphore;

    public function __construct()
    {
        $this->semaphore = new SemaphoreService($this);
    }

    /**
     * @param AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
        $this->config = $app->config->security;
    }

    /**
     * @return SemaphoreService[]
     */
    protected function collectSerializableData(): array
    {
        return [
            "app" => null,
            "config" => null,
            "semaphore" => $this->semaphore,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->semaphore = $data["semaphore"];
    }
}