<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Whether to set in the responseXSRF-TOKEN cookie
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * No needCSRFVerifiedURIList
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
