<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Internal\Services;

use Charcoal\Contracts\Vectors\VectorInterface;

/**
 * Class ServicesBundle
 * @package Charcoal\App\Kernel\Internal\Services
 * @implements  VectorInterface<AppServiceInterface>
 */
final readonly class ServicesBundle implements VectorInterface
{
    private array $services;
    private int $count;

    public function __construct(AppServiceInterface ...$services)
    {
        $this->services = $services;
        $this->count = count($services);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->services);
    }

    /**
     * @return AppServiceInterface[]
     */
    public function getArray(): array
    {
        return $this->services;
    }
}