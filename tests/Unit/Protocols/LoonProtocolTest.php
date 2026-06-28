<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\Loon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoonProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_loon_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Test',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();

        $uri = Loon::buildShadowsocks('ss-pass', $server);

        $this->assertStringContainsString('Shadowsocks', $uri);
        $this->assertStringContainsString('ss.example.com', $uri);
    }

    public function test_loon_build_vmess()
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

        $uri = Loon::buildVmess('uuid-test', $server);

        $this->assertStringContainsString('vmess', $uri);
        $this->assertStringContainsString('over-tls=true', $uri);
    }

    public function test_loon_build_trojan()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Trojan-Test',
            'host' => 'trojan.example.com',
            'port' => 443,
            'password' => 'trojan-pass',
            'protocol_settings' => [
                'network' => 'ws',
                'tls' => 1,
                'tls_settings' => ['server_name' => 'trojan.example.com'],
                'network_settings' => [
                    'path' => '/trojan-ws',
                    'headers' => ['Host' => 'trojan.example.com'],
                ],
            ],
        ])->toArray();

        $uri = Loon::buildTrojan('trojan-pass', $server);

        $this->assertStringContainsString('trojan', $uri);
        $this->assertStringContainsString('transport=ws', $uri);
    }

    public function test_loon_build_hysteria2()
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

        $user = ['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400];
        $uri = Loon::buildHysteria('hy2-pass', $server, $user);

        $this->assertStringContainsString('Hysteria2', $uri);
    }

    public function test_loon_build_vless()
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
                'tls_settings' => ['server_name' => 'vless.example.com', 'allow_insecure' => false],
                'network_settings' => [
                    'path' => '/vless-ws',
                    'headers' => ['Host' => 'vless.example.com'],
                ],
            ],
        ])->toArray();

        $uri = Loon::buildVless('uuid-test', $server);

        $this->assertStringContainsString('VLESS', $uri);
        $this->assertStringContainsString('transport=ws', $uri);
    }
}
