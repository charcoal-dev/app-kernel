<?php
declare(strict_types=1);

namespace Charcoal\App\Kernel\Interfaces\Http;

use Charcoal\HTTP\Router\Controllers\Request;

/**
 * Class RemoteClient
 * @package Charcoal\App\Kernel\Interfaces\Http
 */
readonly class RemoteClient
{
    public string $ipAddress;
    public int $port;
    public ?string $cfConnectingIP;
    public ?string $xForwardedFor;
    public ?string $origin;
    public ?string $userAgent;

    public function __construct(Request $req)
    {
        $this->ipAddress = strval($_SERVER["REMOTE_ADDR"]);
        $this->port = intval($_SERVER["REMOTE_PORT"] ?? 0);
        $this->cfConnectingIP = $req->headers->get("cf-connecting-ip");

        // "X-Forwarded-For" IP Address
        $xff = null;
        if ($req->headers->has("x-forwarded-for")) {
            $xff = explode(",", $req->headers->get("x-forwarded-for"));
            $xff = trim(preg_replace("/[^a-f\d.:]/", "", strtolower($xff[0])));
        }

        $this->xForwardedFor = $xff;

        // Other Headers
        $this->origin = $req->headers->get("referer");
        $this->userAgent = $req->headers->get("user-agent");
    }
}