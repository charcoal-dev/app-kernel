<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Enums;

use Charcoal\App\Kernel\Contracts\Enums\CacheStoreEnumInterface;
use Charcoal\App\Kernel\Enums\EnumContract;
use Charcoal\App\Kernel\Internal\Config\ConfigEnumInterface;

/**
 * Maintains index of concrete Enums implemented by application from contracts.
 * @internal
 */
final class ConcreteEnumsResolver
{
    /** @var array<string, class-string<ConfigEnumInterface>> */
    private array $declared = [];

    /**
     * @param EnumContract $enumContract
     * @param string $class
     * @return $this
     */
    public function declare(EnumContract $enumContract, string $class): self
    {
        if (!enum_exists($class)) {
            throw new \LogicException(sprintf('Enum class "%s" does not exist', $class));
        }

        foreach ([ConfigEnumInterface::class, $enumContract->getContract()] as $contract) {
            if (!is_a($class, $contract, true)) {
                throw new \LogicException(sprintf(
                        'Enum class "%s" does not implement "%s"',
                        $class,
                        $contract)
                );
            }
        }

        $this->declared[$enumContract->name] = $class;
        return $this;
    }

    /**
     * @param EnumContract $enumContract
     * @param string $case
     * @return ConfigEnumInterface
     */
    public function resolve(EnumContract $enumContract, string $case): ConfigEnumInterface
    {
        if (!isset($this->declared[$enumContract->name])) {
            throw new \LogicException(sprintf('Concrete enum for "%s" is not declared', $enumContract->name));
        }

        $enumClass = $this->declared[$enumContract->name];
        $enumCase = $enumClass::find($case);
        if (!$enumCase) {
            throw new \LogicException(sprintf('Enum case "%s" not found in enum "%s"', $case, $enumClass));
        }

        return $enumCase;
    }

    /**
     * @param string $key
     * @return CacheStoreEnumInterface
     */
    public function cacheStore(string $key): CacheStoreEnumInterface
    {
        /** @var CacheStoreEnumInterface */
        return $this->resolve(EnumContract::CacheStoreEnum, $key);
    }
}