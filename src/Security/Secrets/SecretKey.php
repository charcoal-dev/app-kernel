<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security\Secrets;

use Charcoal\App\Kernel\Contracts\Enums\SecretsEnumInterface;
use Charcoal\Base\Enums\Encoding;
use Charcoal\Base\Traits\NotCloneableTrait;
use Charcoal\Base\Traits\NotSerializableTrait;
use Charcoal\Buffers\Frames\Bytes32;
use Charcoal\Cipher\CipherMode;

final readonly class SecretKey implements \Stringable
{
    use NotSerializableTrait;
    use NotCloneableTrait;

    private string $secret;

    public function __construct(
        public SecretsEnumInterface $context,
        public ?CipherMode          $encryption = null,
        public ?Bytes32             $cipherKey = null,
        public ?Encoding            $encoding = null,
    )
    {
    }

    public function __debugInfo(): array
    {
        return [$this->context->getNamespace(),
            $this->context->getReferenceId()];
    }

    public function __toString(): string
    {
        return $this->context->getNamespace() . ":" . $this->context->getReferenceId();
    }

    public function isResolved(): bool
    {
        return isset($this->secret);
    }

    public function resolve(): string
    {
        if (isset($this->secret)) {
            return $this->secret;
        }

    }
}