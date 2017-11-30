<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\IpUtils;

class CheckIPs
{
    public function handle(Request $request, Closure $next)
    {
        foreach ($request->getClientIps() as $ip) {
            if (!$this->isValidIp($ip) && !$this->isValidIpRange($ip)) {
                throw new UnauthorizedException('Your ip is not in the white-list');
            }
        }
        return $next($request);
    }

    private function isValidIp(string $ip): bool
    {
        return in_array($ip, config('app.valid_ips'));
    }

    private function isValidIpRange(string $ip):bool
    {
        return IpUtils::checkIp($ip, config('app.valid_ip_range'));
    }
}
