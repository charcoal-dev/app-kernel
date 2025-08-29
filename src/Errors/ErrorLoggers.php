<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Errors;

use Charcoal\App\Kernel\Contracts\Errors\ErrorLoggerInterface;
use Charcoal\App\Kernel\Support\ErrorHelper;

/**
 * A final class responsible for managing and handling error loggers within the application.
 * This class provides capabilities to register, unregister, and delegate error handling
 * to its registered loggers.
 */
final class ErrorLoggers
{
    /** @var array<class-string<ErrorLoggerInterface>,ErrorLoggerInterface> $loggers */
    private array $loggers;

    /**
     * @internal
     */
    public function __construct(ErrorLoggerInterface ...$loggers)
    {
        $this->loggers = $loggers ?? [];
    }

    /**
     * @internal
     */
    public function register(ErrorLoggerInterface $logger): void
    {
        $this->loggers[$logger::class] = $logger;
    }

    /**
     * @internal
     */
    public function unregister(ErrorLoggerInterface $logger): void
    {
        unset($this->loggers[$logger::class]);
    }

    /**
     * @throws \ErrorException
     * @internal
     */
    public function handleError(ErrorEntry $error): void
    {
        foreach ($this->loggers as $logger) {
            try {
                $logger->handleError($error);
            } catch (\Throwable $e) {
                throw new \ErrorException(ErrorHelper::exception2String($e), 0);
            }
        }
    }

    /**
     * @internal
     */
    public function handleException(\Throwable $exception): void
    {
        foreach ($this->loggers as $logger) {
            $logger->handleException($exception);
        }
    }
}