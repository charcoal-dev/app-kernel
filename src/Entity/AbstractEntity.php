<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Vectors\Strings\StringVector;

/**
 * Class AbstractEntity
 * @package Charcoal\App\Kernel\Entity
 */
abstract class AbstractEntity
{
    use ControlledSerializableTrait;

    /**
     * Returns identifier a UUID or UID or ID or identifier of sorts for instance
     * @return int|string|null
     */
    abstract public function getPrimaryId(): int|string|null;

    /**
     * Restores all possible public properties from serialized dataset
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $prop => $value) {
            if (property_exists($this, $prop) && (!isset($this->$prop) && !is_null($value))) {
                $this->$prop = $value;
            }
        }
    }

    /**
     * Changes value of public property and optionally appends $changeLog StringVector if changed
     * @param string $prop
     * @param mixed $value
     * @param StringVector|null $changeLog
     * @return bool
     */
    public function changeValue(string $prop, mixed $value, ?StringVector $changeLog): bool
    {
        if (!property_exists($this, $prop)) {
            throw new \OutOfBoundsException(sprintf('Class "%s" does not have "%s" property', static::class, $prop));
        }

        if ((!isset($this->$prop) && !is_null($value)) || $this->$prop !== $value) {
            $this->$prop = $value;
            $changeLog?->append($prop);
            return true;
        }

        return false;
    }

    /**
     * @param string ...$props
     * @return array
     */
    public function extract(string ...$props): array
    {
        $dataSet = [];
        foreach ($props as $prop) {
            $dataSet[$prop] = $this->$prop ?? null;
        }

        return $dataSet;
    }
}