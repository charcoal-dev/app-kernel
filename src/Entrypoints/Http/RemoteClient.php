<?php
/*
 * This file is a part of "charcoal-dev/app-kernel" package.
 * https://github.com/charcoal-dev/app-kernel
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/charcoal-dev/app-kernel/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Charcoal\Apps\Kernel\Entrypoints\Http;

use Charcoal\HTTP\Router\Controllers\Request;

/**
 * Class RemoteClient
 * @package Charcoal\Apps\Kernel\Entrypoints\Http
 */
class RemoteClient
{
    /** @var string */
    public readonly string $realIpAddress;
    /** @var string */
    public readonly string $ipAddress;
    /** @var int */
    public readonly int $port;
    /** @var string|null */
    public readonly ?string $origin;
    /** @var string|null */
    public readonly ?string $userAgent;

    /**
     * @param Request $req
     */
    public function __construct(Request $req)
    {
        $this->realIpAddress = strval($_SERVER["REMOTE_ADDR"]);
        $this->port = intval($_SERVER["REMOTE_PORT"] ?? 0);

        // Cloudflare OR X-Forwarded-For IP Address
        if ($req->headers->has("cf-connecting-ip")) {
            $userIpAddr = $req->headers->get("cf-connecting-ip");
        } elseif ($req->headers->has("x-forwarded-for")) {
            $xff = explode(",", $req->headers->get("x-forwarded-for"));
            $userIpAddr = trim(preg_replace('/[^a-f\d.:]/', '', strtolower($xff[0])));
        }

        $this->ipAddress = $userIpAddr ?? $this->realIpAddress;

        // Other Headers
        $this->origin = $req->headers->get("referer");
        $this->userAgent = $req->headers->get("user-agent");
    }
}
