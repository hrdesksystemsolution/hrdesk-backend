<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    protected $proxies = '*';
    protected $headers = 'HEADER_X_FORWARDED_FOR | HEADER_X_FORWARDED_HOST | HEADER_X_FORWARDED_PROTO | HEADER_X_FORWARDED_AWS_ELB';
}
