<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\Tests\App\Fixtures\Orm\Example;

use Charcoal\App\Kernel\Contracts\Orm\Repository\ChecksumAwareRepositoryInterface;
use Charcoal\App\Kernel\Orm\Repository\OrmRepositoryBase;
use Charcoal\App\Kernel\Orm\Repository\Traits\ChecksumAwareRepositoryTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\EntityInsertableTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\EntitySemaphoreLockTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\EntityUpdatableTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\EntityUpsertTrait;
use Charcoal\App\Kernel\Orm\Repository\Traits\StorageHooksInvokerTrait;
use Charcoal\Buffers\Frames\Bytes20;
use Charcoal\Tests\App\Fixtures\Enums\DbConfig;
use Charcoal\Tests\App\Fixtures\Enums\DbTables;

/**
 * Class ExampleRepository
 * @package Charcoal\Tests\App\Fixtures\Orm\Example
 */
class ExampleRepository extends OrmRepositoryBase implements ChecksumAwareRepositoryInterface
{
    use ChecksumAwareRepositoryTrait;
    use EntityInsertableTrait;
    use EntitySemaphoreLockTrait;
    use EntityUpdatableTrait;
    use EntityUpsertTrait;
    use StorageHooksInvokerTrait;

    public function __construct(ExampleModule $module)
    {
        parent::__construct($module, DbTables::Example);
    }

    public function calculateChecksum(ExampleEntity $entity = null): Bytes20
    {
        return $this->entityChecksumCalculate($entity);
    }

    public function verifyChecksum(ExampleEntity $entity = null): bool
    {
        return $this->entityChecksumVerify($entity);
    }

    public function validateChecksum(ExampleEntity $entity = null): void
    {
        $this->entityChecksumValidate($entity);
    }
}