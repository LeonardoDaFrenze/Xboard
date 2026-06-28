<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * Fields that do not need leading and trailing spaces removed
     * @var array<int, string>
     */
    protected $except = [
        'password',
        'password_confirmation',
        'encrypted_data',
        'signature'
    ];
}
