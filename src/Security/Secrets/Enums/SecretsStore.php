<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Security\Secrets\Enums;

/**
 * Class SecretsStore
 * @package Charcoal\App\Kernel\Secrets\Enums
 */
enum SecretsStore
{
    case Docker;
    case Local;

    public function resolver()
    {

    }
}