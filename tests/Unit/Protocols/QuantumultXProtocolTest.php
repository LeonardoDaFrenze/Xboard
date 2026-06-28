<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\QuantumultX;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuantumultXProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_quantumultx_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Test',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();

        $uri = QuantumultX::buildShadowsocks('ss-pass', $server);

        $this->assertStringContainsString('shadowsocks=', $uri);
        $this->assertStringContainsString('method=aes-256-gcm', $uri);
    }

    public function test_quantumultx_build_vmess()
    {
        $server = Server::factory()->make([
            'type' => 'vmess',
            'name' => 'VMess-Test',
            'host' => 'vmess.example.com',
            'port' => 443,
            'password' => 'uuid-test',
            'protocol_settings' => [
                'network' => 'ws',
                'tls' => 1,
                'tls_settings' => ['server_name' => 'vmess.example.com'],
                'network_settings' => [
                    'path' => '/vmess-ws',
                    'headers' => ['Host' => 'vmess.example.com'],
                ],
            ],
        ])->toArray();

        $uri = QuantumultX::buildVmess('uuid-test', $server);

        $this->assertStringContainsString('vmess=', $uri);
    }

    public function test_quantumultx_build_vless()
    {
        $server = Server::factory()->make([
            'type' => 'vless',
            'name' => 'VLESS-Test',
            'host' => 'vless.example.com',
            'port' => 443,
            'password' => 'uuid-test',
            'protocol_settings' => [
                'network' => 'ws',
                'tls' => 1,
                'tls_settings' => ['server_name' => 'vless.example.com'],
                'network_settings' => [
                    'path' => '/vless-ws',
                    'headers' => ['Host' => 'vless.example.com'],
                ],
            ],
        ])->toArray();

        $uri = QuantumultX::buildVless('uuid-test', $server);

        $this->assertStringContainsString('vless=', $uri);
    }

    public function test_quantumultx_build_trojan()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Trojan-Test',
            'host' => 'trojan.example.com',
            'port' => 443,
            'password' => 'trojan-pass',
            'protocol_settings' => [
                'tls' => 1,
                'tls_settings' => ['server_name' => 'trojan.example.com'],
            ],
        ])->toArray();

        $uri = QuantumultX::buildTrojan('trojan-pass', $server);

        $this->assertStringContainsString('trojan=', $uri);
    }

    public function test_quantumultx_build_socks5()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'SOCKS-Test',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = QuantumultX::buildSocks5('socks-pass', $server);

        $this->assertStringContainsString('socks5=', $uri);
    }

    public function test_quantumultx_build_http()
    {
        $server = Server::factory()->make([
            'type' => 'http',
            'name' => 'HTTP-Test',
            'host' => 'http.example.com',
            'port' => 8080,
            'password' => 'http-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = QuantumultX::buildHttp('http-pass', $server);

        $this->assertStringContainsString('http=', $uri);
    }
}
