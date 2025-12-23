<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Orm\Example;

use Charcoal\App\Kernel\Contracts\Orm\Entity\CacheableEntityInterface;
use Charcoal\App\Kernel\Contracts\Orm\Entity\ChecksumAwareEntityInterface;
use Charcoal\App\Kernel\Contracts\Orm\Entity\SemaphoreLockHooksInterface;
use Charcoal\App\Kernel\Contracts\Orm\Entity\StorageHooksInterface;
use Charcoal\App\Kernel\Entity\Traits\ChecksumAwareEntityTrait;
use Charcoal\App\Kernel\Orm\Entity\CacheableEntityTrait;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\Buffers\Types\Bytes20;
use Charcoal\Contracts\Storage\Enums\FetchOrigin;

class ExampleEntity extends OrmEntityBase implements
    ChecksumAwareEntityInterface,
    CacheableEntityInterface,
    SemaphoreLockHooksInterface,
    StorageHooksInterface
{
    public int $id;
    public string $username;

    use CacheableEntityTrait;
    use ChecksumAwareEntityTrait;

    public function getPrimaryId(): int
    {
        return $this->id;
    }

    protected function collectSerializableData(): array
    {
        return ["id" => $this->id,
            "username" => $this->username];
    }

    public function collectChecksumData(): array
    {
        return $this->extract();
    }

    public function getChecksum(): ?Bytes20
    {
        return null;
    }

    public function onLockObtained(): ?string
    {
        return sprintf("ExampleEntity#%d Locked for Editing", $this->id);
    }

    public function onRetrieve(FetchOrigin $origin): ?string
    {
        return sprintf("ExampleEntity#%d Retrieved from %s", $this->id, $origin->name);
    }

    public function onCacheStore(): ?string
    {
        return sprintf("ExampleEntity#%d stored in cache", $this->id);
    }
}