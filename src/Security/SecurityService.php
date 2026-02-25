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
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreProviderEnumInterface;
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
    public SecretsService $secrets;
    public DigestService $digest;
    public ConcurrencyLocks $resourceLocks;

    public function __construct(
        private ?SemaphoreProviderEnumInterface $concurrencyProvider
    )
    {
        $this->semaphore = new SemaphoreService();
        $this->secrets = new SecretsService();
        $this->digest = new DigestService();
        $this->resourceLocks = new ConcurrencyLocks($this->concurrencyProvider);
    }

    /**
     * @param AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
        $this->config = $app->config->security;
        $this->semaphore->bootstrap($this);
        $this->secrets->bootstrap($this);
        $this->resourceLocks->bootstrap($this);
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
            "secrets" => $this->secrets,
            "digest" => null,
            "resourceLocks" => null,
            "concurrencyProvider" => $this->concurrencyProvider,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->concurrencyProvider = $data["concurrencyProvider"];
        $this->semaphore = $data["semaphore"];
        $this->secrets = $data["secrets"];
        $this->digest = new DigestService();
        $this->resourceLocks = new ConcurrencyLocks($this->concurrencyProvider);
    }
}