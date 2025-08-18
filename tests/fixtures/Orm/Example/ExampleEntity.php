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
use Charcoal\App\Kernel\Entity\ChecksumAwareEntityTrait;
use Charcoal\App\Kernel\Orm\Entity\OrmEntityBase;
use Charcoal\App\Kernel\Orm\Entity\CacheableEntityTrait;
use Charcoal\Buffers\Frames\Bytes20;

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
        return $this->extractValues();
    }

    public function getChecksum(): ?Bytes20
    {
        // TODO: Implement getChecksum() method.
    }
}