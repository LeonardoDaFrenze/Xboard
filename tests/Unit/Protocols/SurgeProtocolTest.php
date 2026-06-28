<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\Surge;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SurgeProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_surge_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Test',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();

        $uri = Surge::buildShadowsocks('ss-pass', $server);

        $this->assertStringContainsString('SS-Test = ss', $uri);
        $this->assertStringContainsString('encrypt-method=aes-256-gcm', $uri);
    }

    public function test_surge_build_vmess()
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

        $uri = Surge::buildVmess('uuid-test', $server);

        $this->assertStringContainsString('VMess-Test = vmess', $uri);
        $this->assertStringContainsString('tls=true', $uri);
    }

    public function test_surge_build_trojan()
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

        $uri = Surge::buildTrojan('trojan-pass', $server);

        $this->assertStringContainsString('Trojan-Test = trojan', $uri);
    }

    public function test_surge_build_hysteria2()
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
                'bandwidth' => ['up' => 100, 'down' => 500],
            ],
        ])->toArray();

        $uri = Surge::buildHysteria('hy2-pass', $server);

        $this->assertStringContainsString('Hy2-Test = hysteria2', $uri);
    }

    public function test_surge_build_socks()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'SOCKS-Test',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = Surge::buildSocks('socks-pass', $server);

        $this->assertStringContainsString('SOCKS-Test = socks5', $uri);
    }

    public function test_surge_build_http()
    {
        $server = Server::factory()->make([
            'type' => 'http',
            'name' => 'HTTP-Test',
            'host' => 'http.example.com',
            'port' => 8080,
            'password' => 'http-pass',
            'protocol_settings' => [],
        ])->toArray();

        $uri = Surge::buildHttp('http-pass', $server);

        $this->assertStringContainsString('HTTP-Test = http', $uri);
    }
}
