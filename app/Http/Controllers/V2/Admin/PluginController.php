<?php

namespace App\Http\Controllers\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\Plugin\PluginManager;
use App\Services\Plugin\PluginConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginController extends Controller
{
    protected PluginManager $pluginManager;
    protected PluginConfigService $configService;

    public function __construct(
        PluginManager $pluginManager,
        PluginConfigService $configService
    ) {
        $this->pluginManager = $pluginManager;
        $this->configService = $configService;
    }

    /**
     * Get all plugin types
     */
    public function types()
    {
        return response()->json([
            'data' => [
                [
                    'value' => Plugin::TYPE_FEATURE,
                    'label' => 'Function',
                    'description' => 'Plugins that provide function extensions, such as Telegram login and email notifications',
                    'icon' => '🔧'
                ],
                [
                    'value' => Plugin::TYPE_PAYMENT,
                    'label' => 'Payment method',
                    'description' => 'Plugins that provide payment interfaces, such as Alipay and WeChat Pay',
                    'icon' => '💳'
                ]
            ]
        ]);
    }

    /**
     * Get plugin list
     */
    public function index(Request $request)
    {
        $type = $request->query('type');

        $installedPlugins = Plugin::when($type, function ($query) use ($type) {
            return $query->byType($type);
        })
            ->get()
            ->keyBy('code')
            ->toArray();

        $plugins = [];
        $seenCodes = [];

        foreach ($this->pluginManager->getPluginPaths() as $pluginPath) {
            if (!File::exists($pluginPath)) {
                continue;
            }
            $directories = File::directories($pluginPath);
            foreach ($directories as $directory) {
                $configFile = $directory . '/config.json';
                if (!File::exists($configFile)) {
                    continue;
                }
                $config = json_decode(File::get($configFile), true);
                if (!$config || !isset($config['code'])) {
                    continue;
                }
                $code = $config['code'];

                if (isset($seenCodes[$code])) {
                    continue;
                }
                $seenCodes[$code] = true;

                $pluginType = $config['type'] ?? Plugin::TYPE_FEATURE;
                if ($type && $pluginType !== $type) {
                    continue;
                }

                $installed = isset($installedPlugins[$code]);
                $pluginConfig = $installed ? $this->configService->getConfig($code) : ($config['config'] ?? []);
                $readmeFile = collect(['README.md', 'readme.md'])
                    ->map(fn($f) => $directory . '/' . $f)
                    ->first(fn($path) => File::exists($path));
                $readmeContent = $readmeFile ? File::get($readmeFile) : '';
                $needUpgrade = false;
                if ($installed) {
                    $installedVersion = $installedPlugins[$code]['version'] ?? null;
                    $localVersion = $config['version'] ?? null;
                    if ($installedVersion && $localVersion && version_compare($localVersion, $installedVersion, '>')) {
                        $needUpgrade = true;
                    }
                }
                $isCore = $this->pluginManager->isCorePlugin($code);
                $plugins[] = [
                    'code' => $config['code'],
                    'name' => $config['name'],
                    'version' => $config['version'],
                    'description' => $config['description'],
                    'author' => $config['author'],
                    'type' => $pluginType,
                    'is_installed' => $installed,
                    'is_enabled' => $installed ? $installedPlugins[$code]['is_enabled'] : false,
                    'is_protected' => $isCore,
                    'can_be_deleted' => !$isCore,
                    'config' => $pluginConfig,
                    'readme' => $readmeContent,
                    'need_upgrade' => $needUpgrade,
                    'admin_menus' => $config['admin_menus'] ?? null,
                    'admin_crud' => $config['admin_crud'] ?? null,
                ];
            }
        }

        return response()->json([
            'data' => $plugins
        ]);
    }

    /**
     * Install plugin
     */
    public function install(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $this->pluginManager->install($request->input('code'));
            return response()->json([
                'message' => 'Plugin installation successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plugin installation failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Uninstall plugin
     */
    public function uninstall(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $request->input('code');
        $plugin = Plugin::where('code', $code)->first();
        if ($plugin && $plugin->is_enabled) {
            return response()->json([
                'message' => 'Please disable the plugin before uninstalling'
            ], 400);
        }

        try {
            $this->pluginManager->uninstall($code);
            return response()->json([
                'message' => 'Plugin uninstalled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plugin uninstallation failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upgrade plugin
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        try {
            $this->pluginManager->update($request->input('code'));
            return response()->json([
                'message' => 'Plugin upgrade successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plugin upgrade failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Enable plugin
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $this->pluginManager->enable($request->input('code'));
            return response()->json([
                'message' => 'Plugin enabled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plugin enabling failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Disable plugin
     */
    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $this->pluginManager->disable($request->input('code'));
        return response()->json([
            'message' => 'Plugin disabled successfully'
        ]);

    }

    /**
     * Get plugin configuration
     */
    public function getConfig(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $config = $this->configService->getConfig($request->input('code'));
            return response()->json([
                'data' => $config
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Configuration retrieval failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update plugin configuration
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'config' => 'required|array'
        ]);

        try {
            $this->configService->updateConfig(
                $request->input('code'),
                $request->input('config')
            );

            return response()->json([
                'message' => 'Configuration update successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Configuration update failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload plugin
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:zip',
                'max:10240', // Maximum10MB
            ]
        ], [
            'file.required' => 'Please select a plugin package file',
            'file.file' => 'Invalid file type',
            'file.mimes' => 'The plugin package must be in zip format',
            'file.max' => 'The size of the plugin package cannot exceed 10MB'
        ]);

        try {
            $this->pluginManager->upload($request->file('file'));
            return response()->json([
                'message' => 'Plugin uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plugin upload failed:' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete plugin
     */
    public function delete(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $request->input('code');

// Check if it is a core plugin
        if ($this->pluginManager->isCorePlugin($code)) {
            return response()->json([
                'message' => 'This plugin is a system core plugin and cannot be deleted'
            ], 403);
        }

        try {
            $this->pluginManager->delete($code);
            return response()->json([
                'message' => 'Plugin deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Plugin deletion failed:' . $e->getMessage()
            ], 400);
        }
    }
}