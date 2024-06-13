<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Modules\Objects;

use Charcoal\OOP\Vectors\StringVector;

/**
 * Class AbstractAppObject
 * @package Charcoal\Apps\Kernel\Modules\Objects
 */
abstract class AbstractAppObject
{
    public ObjectRegistrySource $metaObjectSource;
    public int $metaObjectCachedOn;
    public bool $metaObjectRuntime;

    /**
     * @return int[]
     */
    public function __serialize(): array
    {
        return ["metaObjectCachedOn" => $this->metaObjectCachedOn];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $prop => $value) {
            if (!property_exists($this, $prop)) {
                continue;
            }

            $this->$prop = $value;
        }
    }

    /**
     * @return void
     */
    public function beforeCacheStore(): void
    {
        unset($this->metaObjectRuntime, $this->metaObjectSource);
        $this->metaObjectCachedOn = time();
    }

    /**
     * @return array
     */
    abstract public function getRegistryKeys(): array;

    /**
     * This method sets public property value, if changed, returns true otherwise false
     * (and also optionally appends argument StringVector $changeLog with property name)
     * @param string $prop
     * @param mixed $value
     * @param \Charcoal\OOP\Vectors\StringVector|null $changeLog
     * @return bool
     */
    public function changeValue(string $prop, mixed $value, ?StringVector $changeLog): bool
    {
        if (!property_exists($this, $prop)) {
            throw new \OutOfRangeException(sprintf('Class "%s" does not have "%s" property', static::class, $prop));
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
     * @param string ...$props
     * @return array
     */
    public function extractValues(string ...$props): array
    {
        $data = [];
        foreach ($props as $prop) {
            $data[$prop] = $this->$prop;
        }

        return $data;
    }

    /**
     * @param array $data
     * @param string ...$props
     * @return void
     */
    protected function serializeProps(array &$data, string ...$props): void
    {
        foreach ($props as $prop) {
            $data[$prop] = $this->$prop;
        }
    }
}
