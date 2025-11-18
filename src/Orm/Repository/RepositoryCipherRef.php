<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Orm\Repository;

use Charcoal\Cipher\Cipher;
use Charcoal\Security\Secrets\Types\AbstractSecretKey;

/**
 * Class RepositoryCipherRef
 * @package Charcoal\App\Kernel\Orm\Repository
 */
final readonly class RepositoryCipherRef
{
    public function __construct(
        public Cipher            $cipher,
        public AbstractSecretKey $secretKey,
    )
    {
    }
}