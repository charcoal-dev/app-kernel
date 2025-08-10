<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\Cipher\Cipher;

trait CipherAwareRepositoryTrait
{
    protected function getCipher(): Cipher
    {
        if (!$this->cipher) {
            $this->cipher = $this->module->resolveCipherFor($this);
            if (!$this->cipher) {
                throw new \LogicException("No cipher resolved for " . static::class);
            }
        }
        return $this->cipher;
    }
}