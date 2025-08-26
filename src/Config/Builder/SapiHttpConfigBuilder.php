<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\SapiHttpInterfaceConfig;
use Charcoal\App\Kernel\Contracts\EntryPoint\EntryPointEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use Charcoal\Http\Commons\Support\CorsPolicy;
use Charcoal\Http\Router\Config\HttpServer;
use Charcoal\Http\Router\Config\RouterConfig;
use Charcoal\Http\Router\Config\TrustedProxy;

/**
 * Class SapiHttpConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 * @implements ConfigBuilderInterface<SapiHttpInterfaceConfig>
 */
class SapiHttpConfigBuilder implements ConfigBuilderInterface
{
    /** @var array<HttpServer> */
    protected array $hostnames = [];
    /** @var array<TrustedProxy> */
    protected array $trustedProxies = [];
    /** @var array<string,mixed> */
    protected array $routerConfig = [
        "enforceTls" => true,
        "wwwIsAlias" => true,
    ];

    protected bool $corsEnforce = true;
    protected array $corsOrigins = [];
    protected int $corsMaxAge = 0;

    public function __construct(public readonly EntryPointEnumInterface $interface)
    {
    }

    /**
     * Adds a new server instance to the hostname list.
     * @api
     */
    public function addServer(string $hostname, int ...$ports): self
    {
        try {
            $this->hostnames[] = new HttpServer($hostname, ...$ports);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf("Http [%s]: %s",
                $this->interface->name, $e->getMessage()),
                previous: $e);
        }

        return $this;
    }

    /**
     * Adds a trusted proxy to the list of trusted proxies.
     * @api
     */
    public function addTrustedProxy(TrustedProxy $proxy): self
    {
        $this->trustedProxies[] = $proxy;
        return $this;
    }

    /**
     * Configures the router settings.
     * @api
     */
    public function routerConfig(bool $enforceTls = true, bool $wwwIsAlias = true): self
    {
        $this->routerConfig["enforceTls"] = $enforceTls;
        $this->routerConfig["wwwIsAlias"] = $wwwIsAlias;
        return $this;
    }

    /**
     * Configures the CORS policy.
     * @api
     */
    public function corsPolicy(bool $enforce = true, int $maxAge = 3600): self
    {
        $this->corsEnforce = $enforce;
        $this->corsMaxAge = $maxAge;
        if ($this->corsMaxAge < 0) {
            throw new \InvalidArgumentException("Max age must be a positive integer");
        }

        return $this;
    }

    /**
     * Adds an origin to the list of allowed CORS origins.
     * @api
     */
    public function corsAllowOrigin(string $origin): self
    {
        $this->corsOrigins[] = $origin;
        return $this;
    }

    /**
     * @return SapiHttpInterfaceConfig
     */
    final public function build(): SapiHttpInterfaceConfig
    {
        if (!$this->hostnames) {
            throw new \InvalidArgumentException("No hostnames provided for HTTP SAPI interface: " .
                $this->interface->name);
        }

        if (!$this->corsOrigins) {
            throw new \InvalidArgumentException("No CORS origins provided for HTTP SAPI interface: " .
                $this->interface->name);
        }

        return new SapiHttpInterfaceConfig(
            $this->interface,
            new RouterConfig($this->hostnames,
                $this->trustedProxies,
                enforceTls: $this->routerConfig["enforceTls"],
                wwwAlias: $this->routerConfig["wwwIsAlias"],
            ),
            new CorsPolicy(
                $this->corsEnforce,
                $this->corsOrigins,
                maxAge: $this->corsMaxAge,
                withCredentials: false
            )
        );
    }
}