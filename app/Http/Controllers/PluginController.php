<?php

namespace App\Http\Controllers;

use App\Traits\HasPluginConfig;

/**
 * Plugin Controller Base Class
 * 
 * Provides common functionality for all plugin controllers
 */
abstract class PluginController extends Controller
{
    use HasPluginConfig;

    /**
     * Executes checks before performing plugin operations
     */
    protected function beforePluginAction(): ?array
    {
        if (!$this->isPluginEnabled()) {
            return [400, 'Plugin is not enabled'];
        }
        return null;
    }
}