<?php


namespace App\Http\Requests\Admin;

use App\Models\Server;
use Illuminate\Foundation\Http\FormRequest;

class ServerSave extends FormRequest
{
    private const UTLS_RULES = [
        'utls.enabled' => 'nullable|boolean',
        'utls.fingerprint' => 'nullable|string',
    ];

    private const MULTIPLEX_RULES = [
        'multiplex.enabled' => 'nullable|boolean',
        'multiplex.protocol' => 'nullable|string',
        'multiplex.max_connections' => 'nullable|integer',
        'multiplex.min_streams' => 'nullable|integer',
        'multiplex.max_streams' => 'nullable|integer',
        'multiplex.padding' => 'nullable|boolean',
        'multiplex.brutal.enabled' => 'nullable|boolean',
        'multiplex.brutal.up_mbps' => 'nullable|integer',
        'multiplex.brutal.down_mbps' => 'nullable|integer',
    ];

    private const ECH_RULES = [
        'enabled' => 'nullable|boolean',
        'config' => 'nullable|string',
        'query_server_name' => 'nullable|string',
        'key' => 'nullable|string',
    ];

    private const REALITY_RULES = [
        'reality_settings.allow_insecure' => 'nullable|boolean',
        'reality_settings.server_name' => 'nullable|string',
        'reality_settings.server_port' => 'nullable|integer',
        'reality_settings.public_key' => 'nullable|string',
        'reality_settings.private_key' => 'nullable|string',
        'reality_settings.short_id' => 'nullable|string',
    ];

    private const PROTOCOL_RULES = [
        'shadowsocks' => [
            'cipher' => 'required|string',
            'obfs' => 'nullable|string',
            'obfs_settings.path' => 'nullable|string',
            'obfs_settings.host' => 'nullable|string',
            'plugin' => 'nullable|string',
            'plugin_opts' => 'nullable|string',
        ],
        'vmess' => [
            'tls' => 'required|integer',
            'network' => 'required|string',
            'network_settings' => 'nullable|array',
            'rules' => 'nullable|array',
        ],
        'trojan' => [
            'tls' => 'nullable|integer',
            'network' => 'required|string',
            'network_settings' => 'nullable|array',
            'server_name' => 'nullable|string',
            'allow_insecure' => 'nullable|boolean',
        ],
        'hysteria' => [
            'version' => 'required|integer',
            'alpn' => 'nullable|string',
            'obfs.open' => 'nullable|boolean',
            'obfs.type' => 'string|nullable',
            'obfs.password' => 'string|nullable',
            'bandwidth.up' => 'nullable|integer',
            'bandwidth.down' => 'nullable|integer',
            'hop_interval' => 'integer|nullable',
        ],
        'vless' => [
            'tls' => 'required|integer',
            'network' => 'required|string',
            'network_settings' => 'nullable|array',
            'flow' => 'nullable|string',
            'encryption' => 'nullable|array',
            'encryption.enabled' => 'nullable|boolean',
            'encryption.encryption' => 'nullable|string',
            'encryption.decryption' => 'nullable|string',
        ],
        'socks' => [
            'tls' => 'nullable|integer',
        ],
        'naive' => [
            'tls' => 'required|integer',
        ],
        'http' => [
            'tls' => 'required|integer',
        ],
        'tuic' => [
            'version' => 'nullable|integer',
            'congestion_control' => 'nullable|string',
            'alpn' => 'nullable|array',
            'udp_relay_mode' => 'nullable|string',
        ],
        'mieru' => [
            'transport' => 'required|string|in:TCP,UDP',
            'traffic_pattern' => 'string',
        ],
        'anytls' => [
            'tls' => 'nullable|array',
            'alpn' => 'nullable|string',
            'padding_scheme' => 'nullable|array',
        ],
    ];

    private function getBaseRules(): array
    {
        return [
            'type' => 'required|in:' . implode(',', Server::VALID_TYPES),
            'spectific_key' => 'nullable|string',
            'code' => 'nullable|string',
            'show' => '',
            'name' => 'required|string',
            'group_ids' => 'nullable|array',
            'route_ids' => 'nullable|array',
            'parent_id' => 'nullable|integer',
            'machine_id' => 'nullable|integer',
            'enabled' => 'nullable|boolean',
            'host' => 'required',
            'port' => 'required',
            'server_port' => 'required',
            'tags' => 'nullable|array',
            'excludes' => 'nullable|array',
            'ips' => 'nullable|array',
            'rate' => 'required|numeric',
            'rate_time_enable' => 'nullable|boolean',
            'rate_time_ranges' => 'nullable|array',
            'custom_outbounds' => 'nullable|array',
            'custom_routes' => 'nullable|array',
            'cert_config' => 'nullable|array',
            'rate_time_ranges.*.start' => 'required_with:rate_time_ranges|string|date_format:H:i',
            'rate_time_ranges.*.end' => 'required_with:rate_time_ranges|string|date_format:H:i',
            'rate_time_ranges.*.rate' => 'required_with:rate_time_ranges|numeric|min:0',
            'protocol_settings' => 'array',
            'transfer_enable' => 'nullable|integer|min:0',
        ];
    }

    private function getProtocolRules(string $type): array
    {
        $rules = self::PROTOCOL_RULES[$type] ?? [];

        return match ($type) {
            'vmess' => array_merge(
                $rules,
                $this->buildTlsSettingsRules(),
                self::MULTIPLEX_RULES,
                self::UTLS_RULES,
            ),
            'trojan' => array_merge(
                $rules,
                $this->buildTlsSettingsRules(includeRoot: true),
                self::REALITY_RULES,
                self::MULTIPLEX_RULES,
                self::UTLS_RULES,
            ),
            'hysteria' => array_merge(
                $rules,
                $this->buildTlsObjectRules(),
            ),
            'tuic' => array_merge(
                $rules,
                $this->buildTlsObjectRules(),
            ),
            'mieru' => array_merge(
                $rules,
                self::MULTIPLEX_RULES,
            ),
            'vless' => array_merge(
                $rules,
                $this->buildTlsSettingsRules(),
                self::REALITY_RULES,
                self::MULTIPLEX_RULES,
                self::UTLS_RULES,
            ),
            'socks', 'naive', 'http' => array_merge(
                $rules,
                $this->buildTlsSettingsRules(includeRoot: $type !== 'socks'),
            ),
            'anytls' => array_merge(
                $rules,
                $this->buildTlsObjectRules(includeRoot: true),
            ),
            default => $rules,
        };
    }

    private function buildTlsSettingsRules(bool $includeRoot = false): array
    {
        return array_merge(
            $includeRoot ? ['tls_settings' => 'nullable|array'] : [],
            [
                'tls_settings.server_name' => 'nullable|string',
                'tls_settings.allow_insecure' => 'nullable|boolean',
                'tls_settings.ech' => 'nullable|array',
            ],
            $this->prefixRules('tls_settings.ech.', self::ECH_RULES),
        );
    }

    private function buildTlsObjectRules(bool $includeRoot = false): array
    {
        return array_merge(
            $includeRoot ? ['tls' => 'nullable|array'] : [],
            [
                'tls.server_name' => 'nullable|string',
                'tls.allow_insecure' => 'nullable|boolean',
                'tls.ech' => 'nullable|array',
            ],
            $this->prefixRules('tls.ech.', self::ECH_RULES),
        );
    }

    private function prefixRules(string $prefix, array $rules): array
    {
        $result = [];
        foreach ($rules as $field => $rule) {
            $result[$prefix . $field] = $rule;
        }
        return $result;
    }

    public function rules(): array
    {
        $type = $this->input('type');
        $rules = $this->getBaseRules();
        $protocolRules = $this->getProtocolRules($type);

        foreach ($protocolRules as $field => $rule) {
            $rules['protocol_settings.' . $field] = $rule;
        }

        if ($this->input('protocol_settings.network') === 'xhttp') {
            $rules['protocol_settings.network_settings.path'] = 'required|string|regex:#^/#';
            $rules['protocol_settings.network_settings.host'] = 'nullable|string';
            $rules['protocol_settings.network_settings.mode'] = 'nullable|string|in:auto,stream,packet';
            $rules['protocol_settings.network_settings.extra'] = 'nullable|array';
            $rules['protocol_settings.network_settings.extra.headers'] = 'nullable|array';
            $rules['protocol_settings.network_settings.extra.padding'] = 'nullable|boolean';
            $rules['protocol_settings.network_settings.extra.muxx'] = 'nullable|boolean';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'protocol_settings.cipher' => 'Encryption Method',
            'protocol_settings.obfs' => 'Obfuscation Type',
            'protocol_settings.network' => 'Transmission Protocol',
            'protocol_settings.port_range' => 'Port Range',
            'protocol_settings.traffic_pattern' => 'Traffic Pattern',
            'protocol_settings.transport' => 'Transmission Mode',
            'protocol_settings.version' => 'Protocol Version',
            'protocol_settings.password' => 'Password',
            'protocol_settings.handshake.server' => 'Handshake Server',
            'protocol_settings.handshake.server_port' => 'Handshake Port',
            'protocol_settings.multiplex.enabled' => 'Multiplexing',
            'protocol_settings.multiplex.protocol' => 'Multiplexing Protocol',
            'protocol_settings.multiplex.max_connections' => 'Maximum Number of Connections',
            'protocol_settings.multiplex.min_streams' => 'Minimum Stream Count',
            'protocol_settings.multiplex.max_streams' => 'Maximum Stream Count',
            'protocol_settings.multiplex.padding' => 'Multiplexing Padding',
            'protocol_settings.multiplex.brutal.enabled' => 'Brutal Acceleration',
            'protocol_settings.multiplex.brutal.up_mbps' => 'Brutal Upload Rate',
            'protocol_settings.multiplex.brutal.down_mbps' => 'Brutal Download Rate',
            'protocol_settings.utls.enabled' => 'uTLS',
            'protocol_settings.utls.fingerprint' => 'uTLS Fingerprint',
            'protocol_settings.tls_settings.ech.enabled' => 'ECH',
            'protocol_settings.tls_settings.ech.config' => 'ECH Configuration',
            'protocol_settings.tls_settings.ech.query_server_name' => 'ECH Query Domain',
            'protocol_settings.tls_settings.ech.key' => 'ECH Key',
            'protocol_settings.tls.ech.enabled' => 'ECH',
            'protocol_settings.tls.ech.config' => 'ECH Configuration',
            'protocol_settings.tls.ech.query_server_name' => 'ECH Query Domain',
            'protocol_settings.tls.ech.key' => 'ECH Key',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Node Name Cannot Be Empty',
            'group_ids.required' => 'Permission Group Cannot Be Empty',
            'group_ids.array' => 'Incorrect Format for Permission Group',
            'route_ids.array' => 'Incorrect Format for Route Group',
            'parent_id.integer' => 'Incorrect Format for Parent ID',
            'host.required' => 'Node Address Cannot Be Empty',
            'port.required' => 'Connection Port Cannot Be Empty',
            'server_port.required' => 'Backend Service Port Cannot Be Empty',
            'tls.required' => 'TLS Cannot Be Empty',
            'tags.array' => 'Incorrect Format for Tags',
            'rate.required' => 'Rate Ratio Cannot Be Empty',
            'rate.numeric' => 'Incorrect Format for Rate Ratio',
            'network.required' => 'Transmission Protocol Cannot Be Empty',
            'network.in' => 'Incorrect Format for Transmission Protocol',
            'networkSettings.array' => 'Incorrect Configuration for Transmission Protocol',
            'ruleSettings.array' => 'Incorrect Configuration for Rule',
            'tlsSettings.array' => 'Incorrect TLS Configuration',
            'dnsSettings.array' => 'Incorrect DNS Configuration',
            'protocol_settings.*.required' => ':attribute Cannot Be Empty',
            'protocol_settings.*.required_if' => ':attribute Cannot Be Empty',
            'protocol_settings.*.string' => ':attribute Must Be a String',
            'protocol_settings.*.integer' => ':attribute Must Be an Integer',
            'protocol_settings.*.in' => 'Invalid Value for :attribute',
            'transfer_enable.integer' => 'Traffic Limit Must Be an Integer',
            'transfer_enable.min' => 'Traffic Limit Cannot Be Less Than 0',
        ];
    }
}
