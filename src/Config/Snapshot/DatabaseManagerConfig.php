<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Internal\Config\ConfigSnapshotInterface;
use Charcoal\App\Kernel\Support\NetworkHelper;
use Charcoal\Net\Dns\HostnameHelper;

/**
 * Class DatabasesConfig
 * @package Charcoal\App\Kernel\Config
 */
final readonly class DatabaseManagerConfig implements ConfigSnapshotInterface
{
    /** @var array<string, DatabaseConfig> */
    public array $databases;

    /**
     * @param array<string, DatabaseConfig> $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $key => $config) {
            if (!HostnameHelper::isValidHostname($config->host, true, true)) {
                throw new \DomainException("Invalid host IP address for database: " . $key);
            }

            if (!is_null($config->port) && !NetworkHelper::isValidPort($config->port)) {
                throw new \OutOfBoundsException("Invalid port for database: " . $key);
            }

            if (!preg_match('#^[\w.\-\\\/]{2,40}$#i', $config->dbName)) {
                throw new \InvalidArgumentException("Invalid database name: " . $key);
            }

            if (!is_null($config->username) && !preg_match('/^\w{2,40}$/i', $config->username)) {
                throw new \InvalidArgumentException("Invalid username: " . $key);
            }

            if (!is_null($config->password) && !preg_match('/^\w{2,40}$/i', $config->password)) {
                throw new \InvalidArgumentException("Invalid password: " . $key);
            }
        }

        $this->databases = $configs;
    }
}