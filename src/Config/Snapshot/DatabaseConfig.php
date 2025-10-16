<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\Database\Config\DbCredentials;
use Charcoal\Database\Enums\DbConnectionStrategy;
use Charcoal\Database\Enums\DbDriver;

/**
 * Represents the configuration for a database connection.
 */
final readonly class DatabaseConfig extends DbCredentials
{
    public function __construct(
        DbDriver             $driver,
        string               $dbName,
        #[\SensitiveParameter]
        string               $host = "localhost",
        ?int                 $port = null,
        #[\SensitiveParameter]
        ?string              $username = null,
        #[\SensitiveParameter]
        ?string              $password = null,
        #[\SensitiveParameter]
        public ?string       $passwordRef = null,
        DbConnectionStrategy $strategy = DbConnectionStrategy::Lazy,
    )
    {
        if ($password && $this->passwordRef) {
            throw new \LogicException("Cannot set both password and passwordRef");
        }

        parent::__construct($driver, $dbName, $host, $port, $username, $password, $strategy);
    }
}