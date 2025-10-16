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
use Charcoal\Security\Secrets\SecretsKms;

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
        parent::__construct($driver, $dbName, $host, $port, $username, $password, $strategy);

        if ($this->passwordRef) {
            if ($password) {
                throw new \LogicException("Cannot set both password and passwordRef");
            }

            if(!preg_match(SecretsKms::REF_REGEXP, $this->passwordRef)) {
                throw new \InvalidArgumentException("Invalid password reference for database: " . $this->dbName);
            }
        }
    }
}