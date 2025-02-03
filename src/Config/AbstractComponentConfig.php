<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

use Charcoal\App\Kernel\Contracts\ObjectStoreEntityContract;
use Charcoal\App\Kernel\Entity\AbstractEntity;

/**
 * Class AbstractComponentConfig
 * @package Charcoal\App\Kernel\Config
 */
abstract class AbstractComponentConfig extends AbstractEntity implements ObjectStoreEntityContract
{
    public const ?string CONFIG_ID = null;

    /**
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data = [];
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $property) {
            $prop = $property->name;
            if (isset($this->$prop)) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
    }

    /**
     * @return \class-string[]
     */
    public static function childClasses(): array
    {
        return [static::class];
    }

    /**
     * @return string
     */
    final public function getPrimaryId(): string
    {
        if (static::CONFIG_ID === null || static::CONFIG_ID === "") {
            throw new \LogicException(sprintf('CONFIG_ID must be defined in class "%s"', static::class));
        }

        return static::CONFIG_ID;
    }

    /**
     * @return string
     */
    final public function getObjectStoreKey(): string
    {
        return $this->getPrimaryId();
    }
}