<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Database;

use Charcoal\App\Kernel\AbstractApp;
use Charcoal\App\Kernel\Config\Snapshot\DatabaseManagerConfig;
use Charcoal\App\Kernel\Contracts\Domain\AppBootstrappableInterface;
use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\App\Kernel\Internal\Services\AppServiceInterface;
use Charcoal\App\Kernel\Orm\Db\TableRegistry;
use Charcoal\Base\Objects\Traits\ControlledSerializableTrait;
use Charcoal\Base\Objects\Traits\NoDumpTrait;
use Charcoal\Base\Objects\Traits\NotCloneableTrait;
use Charcoal\Base\Registry\Abstracts\AbstractFactoryRegistry;
use Charcoal\Base\Registry\Traits\RegistryKeysLowercaseTrimmed;
use Charcoal\Database\DatabaseClient;

/**
 * Class Databases
 * @package Charcoal\App\Kernel
 * @template-extends AbstractFactoryRegistry<DatabaseClient>
 */
class DatabaseManager extends AbstractFactoryRegistry implements AppServiceInterface, AppBootstrappableInterface
{
    private readonly AbstractApp $app;
    public readonly TableRegistry $tables;

    use ControlledSerializableTrait;
    use RegistryKeysLowercaseTrimmed;
    use NoDumpTrait;
    use NotCloneableTrait;

    public function __construct(public readonly ?DatabaseManagerConfig $config)
    {
        $this->tables = new TableRegistry();
    }

    /**
     * @param AbstractApp $app
     * @return void
     */
    public function bootstrap(AbstractApp $app): void
    {
        $this->app = $app;
    }

    /**
     * @param DatabaseEnumInterface $key
     * @return DatabaseClient
     */
    public function getDb(DatabaseEnumInterface $key): DatabaseClient
    {
        return $this->getExistingOrCreate($key->getConfigKey());
    }

    /**
     * @param string $key
     * @return DatabaseClient
     * @throws \Charcoal\Database\Exceptions\DbConnectionException
     */
    protected function create(string $key): DatabaseClient
    {
        $config = $this->config?->databases[$key];
        if (!$config) {
            throw new \DomainException("Database config not found for key: " . $key);
        }

        $dbPassword = null;
        if ($config->passwordRef) {
            try {
                $secretStore = $this->app->security->secrets->getStore($config->passwordRef->store);
                $namespace = $config->passwordRef->namespace ?
                    $secretStore->namespace($config->passwordRef->namespace) : null;

                $secretKey = $secretStore->load(
                    $config->passwordRef->ref,
                    $config->passwordRef->version,
                    $namespace,
                    allowNullPadding: true
                );

                $secretKey->useSecretEntropy(function (string $entropy) use (&$dbPassword) {
                    $dbPassword = $entropy;
                });
            } catch (\Throwable $t) {
                throw new \RuntimeException("Failed to load database password from secrets: " . $key, 0, $t);
            }

            if (!$dbPassword) {
                throw new \RuntimeException("No database password recovered: " . $key);
            }
        }

        return new DatabaseClient($config, $dbPassword);
    }

    /**
     * Prepares class for serializing,
     * Removes all current Database instances
     * @return array
     */
    public function collectSerializableData(): array
    {
        $data["app"] = null;
        $data["config"] = $this->config;
        $data["tables"] = $this->tables;
        $data["instances"] = null;
        return $data;
    }

    /**
     * Resets class to fresh on unserialize
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->config = $data["config"];
        $this->tables = $data["tables"];
        $this->instances = [];
    }

    /**
     * @return array
     * @api
     */
    public function queriesAggregate(): array
    {
        $queries = [];
        foreach ($this->instances as $dbId => $dbInstance) {
            foreach ($dbInstance->queries as $dbQuery) {
                $queries[] = [
                    "db" => $dbId,
                    "query" => $dbQuery
                ];
            }
        }

        return $queries;
    }

    /**
     * @return int
     * @api
     */
    public function queriesFlush(): int
    {
        $flushed = 0;
        foreach ($this->instances as $db) {
            $flushed += $db->queries->count();
            $db->queries->flush();
        }

        return $flushed;
    }

    public static function unserializeDependencies(): array
    {
        return [static::class, DatabaseManagerConfig::class];
    }
}