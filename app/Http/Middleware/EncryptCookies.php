<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * No encryption neededCookieList of names
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
