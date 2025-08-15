<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Contracts\Enums\DatabaseEnumInterface;
use Charcoal\Base\Traits\NoDumpTrait;
use Charcoal\Database\DbCredentials;

/**
 * Class DbConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 */
class DbConfigBuilder
{
    private array $database = [];

    use NoDumpTrait;

    public function __construct(
        #[\SensitiveParameter]
        public readonly ?string $mysqlRootPassword = null,
    )
    {
    }

    public function set(DatabaseEnumInterface $key, DbCredentials $dbConfig): void
    {
        $this->database[$key->getConfigKey()] = $dbConfig;
    }
}