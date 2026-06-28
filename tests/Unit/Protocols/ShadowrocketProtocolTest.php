<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\Shadowrocket;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShadowrocketProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_shadowrocket_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Test',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => [
                'cipher' => 'chacha20-ietf-poly1305',
            ],
        ])->toArray();

        $uri = Shadowrocket::buildShadowsocks('ss-pass', $server);

        $this->assertStringContainsString('ss://', $uri);
        $this->assertStringContainsString('SS-Test', $uri);
    }

    public function test_shadowrocket_build_vmess()
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
                    'path' => '/vmess',
                    'headers' => ['Host' => 'vmess.example.com'],
                ],
            ],
        ])->toArray();

        $uri = Shadowrocket::buildVmess('uuid-test', $server);

        $this->assertStringContainsString('vmess://', $uri);
        $this->assertStringContainsString('VMess-Test', $uri);
    }

    public function test_shadowrocket_build_trojan()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Trojan-Test',
            'host' => 'trojan.example.com',
            'port' => 443,
            'password' => 'trojan-pass',
            'protocol_settings' => [
                'network' => 'ws',
                'network_settings' => [
                    'path' => '/trojan-ws',
                    'headers' => ['Host' => 'trojan.example.com'],
                ],
            ],
        ])->toArray();

        $uri = Shadowrocket::buildTrojan('trojan-pass', $server);

        $this->assertStringContainsString('trojan://', $uri);
        $this->assertStringContainsString('Trojan-Test', $uri);
    }

    public function test_shadowrocket_reality_build_trojan()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Reality-Test',
            'host' => 'reality.example.com',
            'port' => 443,
            'password' => 'reality-pass',
            'protocol_settings' => [
                'tls' => 2,
                'reality_settings' => [
                    'public_key' => 'test-pub-key',
                    'short_id' => 'test-short-id',
                    'server_name' => 'reality.example.com',
                ],
                'network' => 'tcp',
            ],
        ])->toArray();

        $uri = Shadowrocket::buildTrojan('reality-pass', $server);

        $this->assertStringContainsString('security=reality', $uri);
        $this->assertStringContainsString('pbk=test-pub-key', $uri);
    }

    public function test_shadowrocket_build_socks()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'SOCKS-Test',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = Shadowrocket::buildSocks('socks-pass', $server);

        $this->assertStringContainsString('socks://', $uri);
        $this->assertStringContainsString('SOCKS-Test', $uri);
    }

    public function test_shadowrocket_build_hysteria2()
    {
        $server = Server::factory()->make([
            'type' => 'hysteria',
            'name' => 'Hy2-Test',
            'host' => 'hy2.example.com',
            'port' => 8443,
            'password' => 'hy2-pass',
            'protocol_settings' => [
                'version' => 2,
                'tls' => ['server_name' => 'hy2.example.com', 'allow_insecure' => false],
            ],
        ])->toArray();

        $uri = Shadowrocket::buildHysteria('hy2-pass', $server);

        $this->assertStringContainsString('hysteria2://', $uri);
        $this->assertStringContainsString('Hy2-Test', $uri);
    }

    public function test_shadowrocket_build_tuic()
    {
        $server = Server::factory()->make([
            'type' => 'tuic',
            'name' => 'Tuic-Test',
            'host' => 'tuic.example.com',
            'port' => 1443,
            'password' => 'tuic-pass',
            'protocol_settings' => [
                'tls' => ['server_name' => 'tuic.example.com', 'allow_insecure' => false],
            ],
        ])->toArray();

        $uri = Shadowrocket::buildTuic('tuic-pass', $server);

        $this->assertStringContainsString('tuic://', $uri);
        $this->assertStringContainsString('Tuic-Test', $uri);
    }
}
