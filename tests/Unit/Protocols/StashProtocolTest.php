<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Models\User;
use App\Protocols\Stash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StashProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_stash_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Test',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();

        $proxy = Stash::buildShadowsocks('ss-pass', $server);

        $this->assertEquals('SS-Test', $proxy['name']);
        $this->assertEquals('ss', $proxy['type']);
        $this->assertEquals('aes-256-gcm', $proxy['cipher']);
    }

    public function test_stash_build_vmess()
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
                'ws-opts' => ['path' => '/ws'],
                'network_settings' => [
                    'path' => '/test-ws',
                    'headers' => ['Host' => 'vmess.example.com'],
                ],
            ],
        ])->toArray();

        $proxy = Stash::buildVmess('uuid-test', $server);

        $this->assertEquals('VMess-Test', $proxy['name']);
        $this->assertEquals('vmess', $proxy['type']);
        $this->assertEquals('uuid-test', $proxy['uuid']);
    }

    public function test_stash_build_trojan()
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

        $proxy = Stash::buildTrojan('trojan-pass', $server);

        $this->assertEquals('Trojan-Test', $proxy['name']);
        $this->assertEquals('trojan', $proxy['type']);
        $this->assertArrayHasKey('sni', $proxy);
    }

    public function test_stash_build_hysteria2()
    {
        $server = Server::factory()->make([
            'type' => 'hysteria',
            'name' => 'Hy2-Test',
            'host' => 'hy2.example.com',
            'port' => 8443,
            'password' => 'hy2-pass',
            'protocol_settings' => [
                'version' => 2,
                'tls' => ['allow_insecure' => false],
                'bandwidth' => ['up' => 100, 'down' => 500],
            ],
        ])->toArray();

        $proxy = Stash::buildHysteria('hy2-pass', $server);

        $this->assertEquals('Hy2-Test', $proxy['name']);
        $this->assertEquals('hysteria2', $proxy['type']);
    }

    public function test_stash_build_tuic()
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

        $proxy = Stash::buildTuic('tuic-pass', $server);

        $this->assertEquals('Tuic-Test', $proxy['name']);
        $this->assertEquals('tuic', $proxy['type']);
    }

    public function test_stash_build_socks5()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'SOCKS-Test',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $proxy = Stash::buildSocks5('socks-pass', $server);

        $this->assertEquals('SOCKS-Test', $proxy['name']);
        $this->assertEquals('socks5', $proxy['type']);
    }
}
