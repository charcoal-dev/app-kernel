<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\Base\Vectors\ExceptionVector;

/**
 * Class RuntimeErrorLog
 * @package Charcoal\App\Kernel\Support\Errors
 */
final class RuntimeErrorLog implements ErrorLoggerInterface
{
    /** @var array<ErrorEntry> $errors */
    private array $errors;
    private ExceptionVector $exceptions;

    public function __construct()
    {
        $this->errors = [];
        $this->exceptions = new ExceptionVector();
    }

    /**
     * @param ErrorEntry $error
     * @return void
     * @internal
     */
    public function handleError(ErrorEntry $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @param \Throwable $exception
     * @return void
     * @internal
     */
    public function handleException(\Throwable $exception): void
    {
        $this->exceptions->append($exception);
    }

    /**
     * @return array<ErrorEntry>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return ExceptionVector
     */
    public function getExceptions(): ExceptionVector
    {
        return $this->exceptions;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->errors) + $this->exceptions->count();
    }

    /**
     * @return int
     */
    public function countErrors(): int
    {
        return count($this->errors);
    }

    /**
     * @return int
     */
    public function countExceptions(): int
    {
        return $this->exceptions->count();
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->clearErrors();
        $this->clearExceptions();
    }

    /**
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * @return void
     */
    public function clearExceptions(): void
    {
        $this->exceptions = new ExceptionVector();
    }
}