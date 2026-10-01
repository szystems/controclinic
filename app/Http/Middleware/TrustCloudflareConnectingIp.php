<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustCloudflareConnectingIp
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.trust_cf_connecting_ip')) {
            return $next($request);
        }

        $connecting = $request->headers->get('CF-Connecting-IP');

        if (! is_string($connecting) || filter_var($connecting, FILTER_VALIDATE_IP) === false) {
            return $next($request);
        }

        if (! $this->isPrivateDockerAddress($request->server->get('REMOTE_ADDR'))) {
            return $next($request);
        }

        $request->server->set('REMOTE_ADDR', $connecting);
        $request->headers->remove('X-Forwarded-For');

        return $next($request);
    }

    private function isPrivateDockerAddress(mixed $ip): bool
    {
        if (! is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return false;
        }

        if (str_starts_with($ip, '10.') || str_starts_with($ip, '192.168.')) {
            return true;
        }

        if (! str_starts_with($ip, '172.')) {
            return false;
        }

        $second = (int) explode('.', $ip)[1];

        return $second >= 16 && $second <= 31;
    }
}
