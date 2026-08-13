<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Render terminates TLS at its edge and forwards the request over plain HTTP,
     * marking the original scheme in X-Forwarded-Proto. With no trusted proxy this
     * header is ignored, so Laravel believes the request was insecure and builds
     * http:// redirects and links on an https:// site.
     *
     * '*' rather than a fixed list because the edge address is not stable or
     * published. Safe here: nothing reaches the container except through that edge,
     * so no client is in a position to forge these headers. On a host where the
     * application is directly reachable, list the proxy addresses instead.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
