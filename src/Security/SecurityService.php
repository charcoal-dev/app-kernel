<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Contracts\Enums\SemaphoreScopeEnumInterface;
use Charcoal\App\Kernel\Enums\SemaphoreType;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\Base\Traits\ControlledSerializableTrait;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Base\Traits\NotCloneableTrait;

/**
 * Represents a security service implementation within the application.
 * This final, readonly class ensures that its instances are immutable and cannot be cloned or dumped.
 */
readonly class SecurityService implements AppServiceInterface
{
    use ControlledSerializableTrait;
    use NotCloneableTrait;
    use NoDumpTrait;

    /** @var array{string, SemaphoreService} $semaphore } */
    private array $semaphore;

    public function __construct(AbstractApp $app)
    {
        $this->semaphore = [
            SemaphoreType::Filesystem_Private->name => new SemaphoreService(
                SemaphoreType::Filesystem_Private,
                $app->config->security->semaphorePrivate
            ),

            SemaphoreType::Filesystem_Shared->name => new SemaphoreService(
                SemaphoreType::Filesystem_Shared,
                $app->config->security->semaphoreShared
            )
        ];
    }

    /**
     * @param SemaphoreType|SemaphoreScopeEnumInterface $enum
     * @return SemaphoreService
     */
    public function semaphore(SemaphoreType|SemaphoreScopeEnumInterface $enum): SemaphoreService
    {
        $enum = $enum instanceof SemaphoreScopeEnumInterface ? $enum->getType() : $enum;
        if (!isset($this->semaphore[$enum->name])) {
            throw new \InvalidArgumentException("Semaphore scope was not registered: " . $enum->name);
        }

        return $this->semaphore[$enum->name];
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