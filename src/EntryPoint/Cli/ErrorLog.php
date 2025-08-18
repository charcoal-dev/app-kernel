<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\EntryPoint\Cli;

use Charcoal\App\Kernel\Support\Errors\RuntimeErrorLog;

/**
 * Extended since ErrorLoggers stores subscription by the classname
 * @see \Charcoal\App\Kernel\Errors\ErrorLoggers
 */
class ErrorLog extends RuntimeErrorLog
{
}