<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\App\Kernel\Contracts\Entity\EntityObjectInterface;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\PropertyExtractTrait;
use Charcoal\Base\Objects\Traits\UnserializeBootstrapTrait;

/**
 * Abstract base class for immutable entities.
 * This class provides foundational functionality for entities that are inherently immutable.
 * It uses traits to assist with serialization, property extraction, and unserialization handling.
 */
abstract readonly class AbstractEntityImmutable implements EntityObjectInterface
{
    use ControlledSerializableTrait;
    use PropertyExtractTrait;
    use UnserializeBootstrapTrait;

    abstract public function getPrimaryId(): int|string|null;
}