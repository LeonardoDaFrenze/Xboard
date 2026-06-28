<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\Surfboard;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SurfboardProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_surfboard_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Test',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();

        $uri = Surfboard::buildShadowsocks('ss-pass', $server);

        $this->assertStringContainsString('SS-Test=ss', $uri);
        $this->assertStringContainsString('encrypt-method=aes-256-gcm', $uri);
    }

    public function test_surfboard_build_vmess()
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
                    'path' => '/vmess-ws',
                    'headers' => ['Host' => 'vmess.example.com'],
                ],
            ],
        ])->toArray();

        $uri = Surfboard::buildVmess('uuid-test', $server);

        $this->assertStringContainsString('VMess-Test=vmess', $uri);
        $this->assertStringContainsString('tls=true', $uri);
        $this->assertStringContainsString('ws=true', $uri);
    }

    public function test_surfboard_build_trojan()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Trojan-Test',
            'host' => 'trojan.example.com',
            'port' => 443,
            'password' => 'trojan-pass',
            'protocol_settings' => [
                'tls_settings' => ['server_name' => 'trojan.example.com'],
            ],
        ])->toArray();

        $uri = Surfboard::buildTrojan('trojan-pass', $server);

        $this->assertStringContainsString('Trojan-Test=trojan', $uri);
        $this->assertStringContainsString('trojan.example.com', $uri);
    }

    public function test_surfboard_build_anytls()
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

        $uri = Surfboard::buildAnyTLS('anytls-pass', $server);

        $this->assertStringContainsString('AnyTLS-Test=anytls', $uri);
    }
}
