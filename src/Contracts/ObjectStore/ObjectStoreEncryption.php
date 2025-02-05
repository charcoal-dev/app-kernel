<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Contracts\ObjectStore;

/**
 * Class ObjectStoreEncryption
 * @package Charcoal\App\Kernel\Contracts\ObjectStore
 */
enum ObjectStoreEncryption
{
    case DISABLED;
    case CACHE_ENCRYPTED;
    case CACHE_DECRYPTED;
}