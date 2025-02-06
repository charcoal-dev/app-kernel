<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts;

/**
 * Interface AlertTraceProviderInterface
 * @package Charcoal\App\Kernel\Contracts
 */
interface AlertTraceProviderInterface
{
    public function getTraceInterface(): string;

    public function getTraceId(): ?int;
}