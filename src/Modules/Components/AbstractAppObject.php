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

namespace Charcoal\Apps\Kernel\Modules\Components;

use Charcoal\OOP\Vectors\StringVector;

/**
 * Class AbstractAppObject
 * @package Charcoal\Apps\Kernel\Modules\Components
 */
abstract class AbstractAppObject
{
    public ObjectRegistrySource $metaObjectSource;
    public int $metaObjectCachedOn;
    public bool $metaObjectRuntime;

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

        if (!isset($this->$prop) || $this->$prop !== $value) {
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
    public function extractValues(string ...$props): array
    {
        $data = [];
        foreach ($props as $prop) {
            $data[$prop] = $this->$prop;
        }

        return $data;
    }
}
