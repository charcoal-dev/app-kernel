<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity\Traits;

use Charcoal\Vectors\Strings\StringVector;

/**
 * Provides functionality to change the value of a public property in an entity
 * while optionally maintaining a change log.
 */
trait EntityChangeValueTrait
{
    /**
     * Changes the value of public property and optionally appends $changeLog StringVector if changed
     */
    public function changeValue(string $prop, mixed $value, ?StringVector $changeLog): bool
    {
        if (!property_exists($this, $prop)) {
            throw new \OutOfBoundsException(sprintf('Entity "%s" does not have "%s" property', static::class, $prop));
        }

        if ((!isset($this->$prop) && !is_null($value)) || $this->$prop !== $value) {
            $this->$prop = $value;
            $changeLog?->append($prop);
            return true;
        }

        return false;
    }
}
