<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Module;

use Charcoal\App\Kernel\Orm\AbstractOrmModule;
use Charcoal\Cipher\Cipher;
use Charcoal\OOP\Traits\ControlledSerializableTrait;

/**
 * Class AbstractModuleComponent
 * @package Charcoal\App\Kernel\Module
 */
abstract class AbstractModuleComponent
{
    private ?Cipher $cipher = null;

    use ControlledSerializableTrait;

    public function __construct(
        public readonly AbstractOrmModule $module,
    )
    {
    }

    /**
     * @return array
     */
    protected function collectSerializableData(): array
    {
        $data["cipher"] = null;
        $data["module"] = $this->module;
        return $data;
    }

    /**
     * @param array $data
     * @return void
     */
    protected function onUnserialize(array $data): void
    {
        /** @noinspection PhpSecondWriteToReadonlyPropertyInspection */
        $this->module = $data["module"];
        $this->cipher = null;
    }

    /**
     * @return Cipher
     */
    protected function getCipher(): Cipher
    {
        if (!$this->cipher) {
            $this->cipher = $this->module->getCipher($this);
            if (!$this->cipher) {
                throw new \LogicException("No cipher resolved for " . static::class);
            }
        }
        return $this->cipher;
    }
}