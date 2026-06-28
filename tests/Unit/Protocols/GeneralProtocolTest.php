<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Protocols\General;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GeneralProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Server',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();

        $uri = General::buildShadowsocks('ss-pass', $server);

        $this->assertStringContainsString('ss://', $uri);
    }

    public function test_general_build_vmess()
    {
        $server = Server::factory()->make([
            'type' => 'vmess',
            'name' => 'VMess-Test',
            'host' => 'vmess.example.com',
            'port' => 443,
            'password' => 'uuid-test',
            'protocol_settings' => [
                'network' => 'ws',
                'tls' => true,
                'tls_settings' => ['server_name' => 'vmess.example.com'],
                'network_settings' => [
                    'path' => '/test-ws',
                    'headers' => ['Host' => 'vmess.example.com'],
                ],
            ],
        ])->toArray();

        $uri = General::buildVmess('uuid-test', $server);

        $this->assertStringContainsString('vmess://', $uri);
        $parts = explode('vmess://', $uri);
        $decoded = json_decode(base64_decode(trim($parts[1])), true);
        $this->assertNotNull($decoded);
        $this->assertEquals('VMess-Test', $decoded['ps']);
    }

    public function test_general_build_vless()
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

        $uri = General::buildVless('uuid-test', $server);

        $this->assertStringContainsString('vless://', $uri);
        $this->assertStringContainsString('VLESS-Test', $uri);
    }

    public function test_general_build_trojan()
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

        $uri = General::buildTrojan('trojan-pass', $server);

        $this->assertStringContainsString('trojan://', $uri);
        $this->assertStringContainsString('Trojan-Test', $uri);
    }

    public function test_general_build_hysteria2()
    {
        $server = Server::factory()->make([
            'type' => 'hysteria',
            'name' => 'Hy2-Test',
            'host' => 'hy2.example.com',
            'port' => 8443,
            'password' => 'hy2-pass',
            'protocol_settings' => [
                'version' => 2,
                'tls' => ['server_name' => 'hy2.example.com'],
            ],
        ])->toArray();

        $uri = General::buildHysteria('hy2-pass', $server);

        $this->assertStringContainsString('hysteria2://', $uri);
        $this->assertStringContainsString('Hy2-Test', $uri);
    }

    public function test_general_build_tuic()
    {
        $server = Server::factory()->make([
            'type' => 'tuic',
            'name' => 'Tuic-Test',
            'host' => 'tuic.example.com',
            'port' => 1443,
            'password' => 'tuic-pass',
            'protocol_settings' => [
                'tls' => ['server_name' => 'tuic.example.com'],
            ],
        ])->toArray();

        $uri = General::buildTuic('tuic-pass', $server);

        $this->assertStringContainsString('tuic://', $uri);
        $this->assertStringContainsString('Tuic-Test', $uri);
    }

    public function test_general_build_socks()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'SOCKS-Test',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = General::buildSocks('socks-pass', $server);

        $this->assertStringContainsString('socks://', $uri);
    }

    public function test_general_build_http()
    {
        $server = Server::factory()->make([
            'type' => 'http',
            'name' => 'HTTP-Test',
            'host' => 'http.example.com',
            'port' => 8080,
            'password' => 'http-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = General::buildHttp('http-pass', $server);

        $this->assertStringContainsString('http://', $uri);
    }

    public function test_general_build_anytls()
    {
        $server = Server::factory()->make([
            'type' => 'anytls',
            'name' => 'AnyTLS-Test',
            'host' => 'anytls.example.com',
            'port' => 443,
            'password' => 'anytls-pass',
            'protocol_settings' => [
                'tls' => ['server_name' => 'anytls.example.com'],
            ],
        ])->toArray();

        $uri = General::buildAnyTLS('anytls-pass', $server);

        $this->assertStringContainsString('anytls://', $uri);
    }
}
