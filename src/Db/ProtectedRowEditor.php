<?php
declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Db;

use Charcoal\Apps\Kernel\AbstractApp;
use Charcoal\Apps\Kernel\Modules\Objects\AbstractAppObject;
use Charcoal\Semaphore\Filesystem\FileLock;

/**
 * Class ProtectedRowEditor
 * @package Charcoal\Apps\Kernel\Db
 */
abstract class ProtectedRowEditor
{
    protected AbstractAppObject $object;
    protected FileLock $lock;

    /**
     * @param AbstractApp $app
     * @param string $semaphoreLockId
     * @param bool $autoReleaseLock
     * @param int $lockTimeout
     * @throws \Charcoal\Semaphore\Exception\SemaphoreLockException
     */
    public function __construct(
        protected readonly AbstractApp $app,
        protected readonly string      $semaphoreLockId,
        protected readonly bool        $autoReleaseLock = true,
        protected readonly int         $lockTimeout = 0
    )
    {
        // First obtain the required lock
        $this->lock = $app->kernel->semaphore->obtainLock(
            $this->semaphoreLockId,
            $this->lockTimeout > 0 ? 0.25 : null,
            $this->lockTimeout
        );

        if ($this->autoReleaseLock) {
            $this->lock->setAutoRelease();
        }

        // Resolve required object
        try {
            $this->resolve();
        } catch (\Exception $t) {
            try {
                $this->lock->releaseLock();
            } catch (\Exception) {
            }

            throw $t;
        }
    }

    abstract protected function resolve(): AbstractAppObject;

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return isset($this->lock) && $this->lock->isLocked();
    }

    /**
     * @return void
     * @throws \Charcoal\Semaphore\Exception\SemaphoreLockException
     */
    public function close(): void
    {
        if ($this->isLocked()) {
            $this->lock->releaseLock();
            unset($this->lock);
        }
    }
}