<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Snapshot;

use Charcoal\App\Kernel\Support\NetworkHelper;

/**
 * Class DatabasesConfig
 * @package Charcoal\App\Kernel\Config
 */
final readonly class DatabaseManagerConfig
{
    /** @var array<string, DatabaseConfig> */
    public array $databases;

    /**
     * @param array<string, DatabaseConfig> $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $key => $config) {
            if (!NetworkHelper::isValidHostname($config->host)) {
                throw new \DomainException("Invalid host IP address for database: " . $key);
            }

            if (is_int($config->port) && !NetworkHelper::isValidPort($config->port)) {
                throw new \OutOfBoundsException("Invalid port for database: " . $key);
            }

            if (!preg_match('/^\w{2,40}$/i', $config->dbName)) {
                throw new \InvalidArgumentException("Invalid database name: " . $key);
            }

            if (!$config->username && !preg_match('/^\w{2,40}$/i', $config->username)) {
                throw new \InvalidArgumentException("Invalid username: " . $key);
            }

            if (!$config->password && !preg_match('/^\w{2,40}$/i', $config->password)) {
                throw new \InvalidArgumentException("Invalid password: " . $key);
            }
        }

        $this->databases = $configs;
    }
}