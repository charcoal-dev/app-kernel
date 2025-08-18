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
 * Class ErrorLoggers
 * @package Charcoal\App\Kernel\Errors
 * @internal
 */
final class ErrorLoggers
{
    /** @var array<class-string<ErrorLoggerInterface>,ErrorLoggerInterface> $loggers */
    private array $loggers;

    public function __construct(ErrorLoggerInterface ...$loggers)
    {
        $this->loggers = $loggers ?? [];
    }

    public function register(ErrorLoggerInterface $logger): void
    {
        $this->loggers[$logger::class] = $logger;
    }

    public function unregister(ErrorLoggerInterface $logger): void
    {
        unset($this->loggers[$logger::class]);
    }

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

    public function handleException(\Throwable $exception): void
    {
        foreach ($this->loggers as $logger) {
            $logger->handleException($exception);
        }
    }
}