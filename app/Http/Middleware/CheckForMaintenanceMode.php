<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;

class CheckForMaintenanceMode extends PreventRequestsDuringMaintenance
{
    /**
     * Maintenance Mode WhitelistURI
     * @var array<int, string>
     */
    protected $except = [
// Example:
        // '/api/health-check',
        // '/status'
    ];
}
