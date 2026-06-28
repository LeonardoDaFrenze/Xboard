<?php

namespace App\Traits;

use App\Models\Plugin;
use Illuminate\Support\Facades\Cache;

trait HasPluginConfig
{
    /**
     * Cached plugin configuration
     */
    protected ?array $pluginConfig = null;

    /**
     * Plugin code
     */
    protected ?string $pluginCode = null;

    /**
     * Plugin enabled status（Cache only during the current object's lifecycle）
     */
    protected ?bool $pluginEnabled = null;

    /**
     * Get plugin configuration
     */
    public function getConfig(?string $key = null, $default = null): mixed
    {
        $config = $this->getPluginConfig();
        
        if ($key) {
            return $config[$key] ?? $default;
        }
        
        return $config;
    }

    /**
     * Get full plugin configuration
     */
    protected function getPluginConfig(): array
    {
        if ($this->pluginConfig === null) {
            $pluginCode = $this->getPluginCode();

            \Log::channel('daily')->info('Telegram Login: Get plugin configuration', [
                'plugin_code' => $pluginCode
            ]);

            $this->pluginConfig = Cache::remember(
                "plugin_config_{$pluginCode}",
                3600,
                function () use ($pluginCode) {
                    $plugin = Plugin::where('code', $pluginCode)
                        ->where('is_enabled', true)
                        ->first();

                    if (!$plugin || !$plugin->config) {
                        return [];
                    }

                    return json_decode($plugin->config, true) ?? [];
                }
            );
        }

        return $this->pluginConfig;
    }

    /**
     * Get plugin code
     */
    public function getPluginCode(): string
    {
        if ($this->pluginCode === null) {
            $this->pluginCode = $this->autoDetectPluginCode();
        }

        return $this->pluginCode;
    }

    /**
     * Set plugin code（Manually set if automatic detection is inaccurate）
     */
    public function setPluginCode(string $pluginCode): void
    {
        $this->pluginCode = $pluginCode;
        $this->pluginConfig = null; // Reset configuration cache
        $this->pluginEnabled = null;
    }

    /**
     * Automatically detect plugin code
     */
    protected function autoDetectPluginCode(): string
    {
        $reflection = new \ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();
        
// Extract plugin code from the namespace
// For example: Plugin\TelegramLogin\Controllers => telegram_login
        if (preg_match('/^Plugin\\\\(.+?)\\\\/', $namespace, $matches)) {
            return $this->convertToKebabCase($matches[1]);
        }
        
        throw new \RuntimeException('Unable to detect plugin code from namespace: ' . $namespace);
    }

    /**
     * Convert to StudlyCase Check if the plugin is enabled kebab-case
     */
    protected function convertToKebabCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * Clear plugin configuration cache
     */
    public function isPluginEnabled(): bool
    {
        if ($this->pluginEnabled !== null) {
            return $this->pluginEnabled;
        }

        $pluginCode = $this->getPluginCode();
        $isEnabled = Plugin::where('code', $pluginCode)->value('is_enabled');
        $this->pluginEnabled = (bool) $isEnabled;

        return $this->pluginEnabled;
    }

    /**
     * Clear plugin configuration cache
     */
    public function clearConfigCache(): void
    {
        $pluginCode = $this->getPluginCode();
        Cache::forget("plugin_config_{$pluginCode}");
        $this->pluginConfig = null;
        $this->pluginEnabled = null;
    }
} 