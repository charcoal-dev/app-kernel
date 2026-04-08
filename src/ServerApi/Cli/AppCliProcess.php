<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\ServerApi\Cli;

use Charcoal\App\Kernel\ServerApi\Cli\Traits\ProcessConcurrencyTrait;
use Charcoal\App\Kernel\ServerApi\Cli\Traits\ProcessDefaultTrait;
use Charcoal\Cli\Process\AbstractCliProcess;

/**
 * Abstract class representing a Command Line Interface (CLI) process
 * with default behaviors and concurrency handling mechanisms.
 */
abstract class AppCliProcess extends AbstractCliProcess
{
    use ProcessDefaultTrait;
    use ProcessConcurrencyTrait;

    /**
     * @param AppCliHandler $cli
     * @throws \Charcoal\App\Kernel\Concurrency\ConcurrencyLockException
     */
    public function __construct(AppCliHandler $cli)
    {
        parent::__construct($cli);
        $this->initializeCliDefaults();
        $this->initializeConcurrency();
    }
}