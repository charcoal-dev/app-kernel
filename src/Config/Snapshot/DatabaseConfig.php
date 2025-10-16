<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Security\Secrets\SecretRef;
use Charcoal\Database\Config\DbCredentials;
use Charcoal\Database\Enums\DbConnectionStrategy;
use Charcoal\Database\Enums\DbDriver;

/**
 * Represents the configuration for a database connection.
 */
final readonly class DatabaseConfig extends DbCredentials
{
    public ?SecretRef $passwordRef;

    public function __construct(
        DbDriver              $driver,
        string                $dbName,
        #[\SensitiveParameter]
        string                $host = "localhost",
        ?int                  $port = null,
        #[\SensitiveParameter]
        ?string               $username = null,
        #[\SensitiveParameter]
        null|string|SecretRef $password = null,
        DbConnectionStrategy  $strategy = DbConnectionStrategy::Lazy,
    )
    {
        $this->passwordRef = $password instanceof SecretRef ? $password : null;
        if ($this->passwordRef) {
            $password = null;
        }

        parent::__construct($driver, $dbName, $host, $port, $username, $password, $strategy);
    }
}