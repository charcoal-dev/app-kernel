<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Errors;

use Charcoal\Apps\Kernel\Errors;

/**
 * Class ErrorMsg
 * @package Charcoal\Apps\Kernel\Errors
 */
class ErrorMsg
{
    public readonly string $filePath;
    public readonly string $levelStr;
    public readonly ?array $backtrace;

    /**
     * @param \Charcoal\Apps\Kernel\Errors $errors
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public function __construct(
        Errors                 $errors,
        public readonly int    $level,
        public readonly string $message,
        string                 $file,
        public readonly int    $line,
    )
    {
        $this->filePath = $errors->getOffsetFilepath($file);
        $this->levelStr = $errors->getErrorLevelStr($this->level);
        $this->backtrace = ($errors->debugBacktraceLevel <= $this->level) ?
            array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), $errors->backtraceOffset) : null;
    }
}

