<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Support\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Errors\ErrorEntry;
use Charcoal\Vectors\Support\ExceptionBag;

/**
 * This class implements the ErrorLoggerInterface and provides functionality
 * for managing runtime errors and exceptions. It keeps a collection of
 * error entries and exceptions, and provides methods to handle, retrieve,
 * clear, and count these entries.
 */
final class RuntimeErrorLog implements ErrorLoggerInterface
{
    /** @var array<ErrorEntry> $errors */
    private array $errors;
    private ExceptionBag $exceptions;

    public function __construct()
    {
        $this->errors = [];
        $this->exceptions = new ExceptionBag();
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
     * @return ExceptionBag
     * @api
     */
    public function getExceptions(): ExceptionBag
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
     * @api
     */
    public function countErrors(): int
    {
        return count($this->errors);
    }

    /**
     * @return int
     * @api
     */
    public function countExceptions(): int
    {
        return $this->exceptions->count();
    }

    /**
     * @return void
     * @api
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
        $this->exceptions = new ExceptionBag();
    }
}