<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Entity;

use Charcoal\Base\Vectors\StringVector;

/**
 * Class AbstractEntity
 * @package Charcoal\App\Kernel\Entity
 */
abstract class AbstractEntity
{
    /**
     * Returns identifier a UUID or UID or ID or identifier of sorts for instance
     * @return int|string|null
     */
    abstract public function getPrimaryId(): int|string|null;

    /**
     * Forces all child classes to explicitly implement "collectSerializableData" method
     * @return array
     */
    public function __serialize(): array
    {
        return $this->collectSerializableData();
    }

    /**
     * Returns all serializable properties, can be used with "extractValues" method of this class
     * @return array
     */
    abstract protected function collectSerializableData(): array;

    /**
     * Restores all possible public properties from serialized dataset
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $prop => $value) {
            if (property_exists($this, $prop)) {
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
     * Extracts argument properties as associative array
     * @param array $dataSet
     * @param string[] $props
     */
    public function extractValues(array &$dataSet, string ...$props): void
    {
        foreach ($props as $prop) {
            $dataSet[$prop] = $this->$prop;
        }
    }
}