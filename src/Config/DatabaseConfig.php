<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config;

use Charcoal\App\Kernel\Contracts\Enums\SecretsEnumInterface;
use Charcoal\Database\Config\DbCredentials;
use Charcoal\Database\Enums\DbConnectionStrategy;
use Charcoal\Database\Enums\DbDriver;

/**
 * Class DatabaseConfig
 * @package Charcoal\App\Kernel\Config
 */
final readonly class DatabaseConfig extends DbCredentials
{
    public function __construct(
        DbDriver                     $driver,
        string                       $dbName,
        #[\SensitiveParameter]
        string                       $host = "localhost",
        ?int                         $port = null,
        #[\SensitiveParameter]
        ?string                      $username = null,
        #[\SensitiveParameter]
        ?string                      $password = null,
        #[\SensitiveParameter]
        public ?SecretsEnumInterface $passwordRef = null,
        DbConnectionStrategy         $strategy = DbConnectionStrategy::Lazy,
    )
    {
        parent::__construct($driver, $dbName, $host, $port, $username, $password, $strategy);
    }
}