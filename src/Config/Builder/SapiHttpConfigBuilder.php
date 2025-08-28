<?php
/**
 * Part of the "charcoal-dev/app-kernel" package.
 * @link https://github.com/charcoal-dev/app-kernel
 */

declare(strict_types=1);

namespace Charcoal\App\Kernel\Config\Builder;

use Charcoal\App\Kernel\Config\Snapshot\SapiHttpInterfaceConfig;
use Charcoal\App\Kernel\Contracts\Enums\SapiEnumInterface;
use Charcoal\App\Kernel\Internal\Config\ConfigBuilderInterface;
use Charcoal\Http\Commons\Enums\HeaderKeyValidation;
use Charcoal\Http\Commons\Enums\ParamKeyValidation;
use Charcoal\Http\Commons\Support\CorsPolicy;
use Charcoal\Http\Server\Config\RequestConstraints;
use Charcoal\Http\Server\Config\ServerConfig;
use Charcoal\Http\Server\Config\VirtualHost;
use Charcoal\Http\TrustProxy\Config\TrustedProxy;

/**
 * Class SapiHttpConfigBuilder
 * @package Charcoal\App\Kernel\Config\Builder
 * @implements ConfigBuilderInterface<SapiHttpInterfaceConfig>
 */
class SapiHttpConfigBuilder implements ConfigBuilderInterface
{
    /** @var array<VirtualHost> */
    protected array $hostnames = [];
    /** @var array<TrustedProxy> */
    protected array $trustedProxies = [];

    protected bool $serverTlsEnforce = true;
    protected bool $serverWwwSupport = true;
    protected ?RequestConstraints $requestConstraints = null;
    protected bool $corsEnforce = true;
    protected array $corsOrigins = [];
    protected int $corsMaxAge = 0;

    public function __construct(public readonly SapiEnumInterface $interface)
    {
    }

    /**
     * Adds a new server instance to the hostname list.
     * @api
     */
    public function addServer(string $hostname, int ...$ports): self
    {
        try {
            $this->hostnames[] = new VirtualHost($hostname, ...$ports);
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
    public function routerConfig(bool $enforceTls = true, bool $wwwSupport = true): self
    {
        $this->serverTlsEnforce = $enforceTls;
        $this->serverWwwSupport = $wwwSupport;
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
     * @return $this
     * @api
     */
    public function perRequestConstraints(
        int                 $maxUriBytes = 256,
        int                 $maxHeaders = 40,
        int                 $maxHeaderLength = 256,
        HeaderKeyValidation $headerKeyValidation = HeaderKeyValidation::RFC7230,
        ParamKeyValidation  $paramKeyValidation = ParamKeyValidation::STRICT,
        int                 $maxBodyBytes = 10240,
        int                 $maxParams = 32,
        int                 $maxParamLength = 256,
        int                 $dtoMaxDepth = 3
    ): self
    {
        $this->requestConstraints = new RequestConstraints(
            $maxUriBytes,
            $maxHeaders,
            $maxHeaderLength,
            $headerKeyValidation,
            $paramKeyValidation,
            $maxBodyBytes,
            $maxParams,
            $maxParamLength,
            $dtoMaxDepth
        );

        return $this;
    }

    /**
     * @return SapiHttpInterfaceConfig
     */
    final public function build(): SapiHttpInterfaceConfig
    {
        if (!$this->hostnames) {
            throw new \BadMethodCallException("No hostnames provided for HTTP SAPI interface: " .
                $this->interface->name);
        }

        if (!$this->corsOrigins) {
            throw new \BadMethodCallException("No CORS origins provided for HTTP SAPI interface: " .
                $this->interface->name);
        }

        if (!$this->requestConstraints) {
            throw new \BadMethodCallException("No perRequestConstraints provided for HTTP SAPI interface: " .
                $this->interface->name);
        }

        return new SapiHttpInterfaceConfig(
            $this->interface,
            new ServerConfig(
                $this->hostnames,
                $this->trustedProxies,
                new CorsPolicy(
                    $this->corsEnforce,
                    $this->corsOrigins,
                    maxAge: $this->corsMaxAge,
                    withCredentials: false
                ),
                $this->requestConstraints,
                enforceTls: $this->serverTlsEnforce,
                wwwSupport: $this->serverWwwSupport,
            )
        );
    }
}