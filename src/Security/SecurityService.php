<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Base\Traits\ControlledSerializableTrait;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;

/**
 * Represents a security service implementation within the application.
 * This final, readonly class ensures that its instances are immutable and cannot be cloned or dumped.
 */
final readonly class SecurityService implements AppServiceInterface
{
    use ControlledSerializableTrait;
    use NotCloneableTrait;
    use NoDumpTrait;

    public SemaphoreService $semaphore;

    public function __construct(AbstractApp $app)
    {
        $this->semaphore = new SemaphoreService($app->config->security->semaphoreDirectory);
    }

    /**
     * @return SemaphoreService[]
     */
    protected function collectSerializableData(): array
    {
        return [
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