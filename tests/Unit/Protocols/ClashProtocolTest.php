<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\Clash;
use App\Protocols\ClashMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClashProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_clash_protocol_classes_exist()
    {
        $this->assertTrue(class_exists(Clash::class));
        $this->assertTrue(class_exists(ClashMeta::class));
    }

    public function test_clash_build_shadowsocks_proxy()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Server',
            'host' => '1.2.3.4',
            'port' => 8443,
            'password' => 'testpass',
            'protocol_settings' => [
                'cipher' => 'aes-256-gcm',
            ],
        ])->toArray();

        $proxy = Clash::buildShadowsocks('testpass', $server);

        $this->assertEquals('SS-Server', $proxy['name']);
        $this->assertEquals('ss', $proxy['type']);
        $this->assertEquals('1.2.3.4', $proxy['server']);
        $this->assertEquals(8443, $proxy['port']);
        $this->assertEquals('aes-256-gcm', $proxy['cipher']);
        $this->assertTrue($proxy['udp']);
    }

    public function test_clash_build_vmess_proxy()
    {
        $server = Server::factory()->make([
            'type' => 'vmess',
            'name' => 'VMESS-Test',
            'host' => '5.6.7.8',
            'port' => 443,
            'password' => 'uuid-1234',
            'protocol_settings' => [
                'network' => 'ws',
                'tls' => true,
                'tls_settings' => ['server_name' => 'example.com', 'allow_insecure' => false],
                'network_settings' => [
                    'path' => '/ws',
                    'headers' => ['Host' => 'example.com'],
                ],
            ],
        ])->toArray();

        $proxy = Clash::buildVmess('uuid-1234', $server);

        $this->assertEquals('VMESS-Test', $proxy['name']);
        $this->assertEquals('vmess', $proxy['type']);
        $this->assertEquals('ws', $proxy['network']);
        $this->assertTrue($proxy['tls']);
        $this->assertEquals('uuid-1234', $proxy['uuid']);
    }

    public function test_clash_build_trojan_proxy()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Trojan-Server',
            'host' => 'trojan.example.com',
            'port' => 443,
            'password' => 'trojan-pass',
            'protocol_settings' => [
                'network' => 'tcp',
                'tls_settings' => [
                    'server_name' => 'trojan.example.com',
                    'allow_insecure' => false,
                ],
            ],
        ])->toArray();

        $proxy = Clash::buildTrojan('trojan-pass', $server);

        $this->assertEquals('Trojan-Server', $proxy['name']);
        $this->assertEquals('trojan', $proxy['type']);
        $this->assertEquals('trojan-pass', $proxy['password']);
        $this->assertArrayHasKey('sni', $proxy);
    }

    public function test_clash_build_socks5_proxy()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'SOCKS-Test',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $proxy = Clash::buildSocks5('socks-pass', $server);

        $this->assertEquals('SOCKS-Test', $proxy['name']);
        $this->assertEquals('socks5', $proxy['type']);
        $this->assertEquals('socks-pass', $proxy['username']);
        $this->assertTrue($proxy['udp']);
    }

    public function test_clash_build_http_proxy()
    {
        $server = Server::factory()->make([
            'type' => 'http',
            'name' => 'HTTP-Proxy',
            'host' => 'http.example.com',
            'port' => 8080,
            'password' => 'http-pass',
            'protocol_settings' => [],
        ])->toArray();

        $proxy = Clash::buildHttp('http-pass', $server);

        $this->assertEquals('HTTP-Proxy', $proxy['name']);
        $this->assertEquals('http', $proxy['type']);
        $this->assertEquals('http-pass', $proxy['username']);
    }
}
