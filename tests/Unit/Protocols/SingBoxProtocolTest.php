<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Protocols\SingBox;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SingBoxProtocolTest extends TestCase
{
    use RefreshDatabase;

    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    public function test_singbox_build_shadowsocks()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Server',
            'host' => 'ss.example.com',
            'port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => [
                'cipher' => 'aes-256-gcm',
            ],
        ])->toArray();

        $singBox = new SingBox(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], [$server], 'sing-box', '1.8.0');
        $config = $this->invokeMethod($singBox, 'buildShadowsocks', ['ss-pass', $server]);

        $this->assertEquals('SS-Server', $config['tag']);
        $this->assertEquals('shadowsocks', $config['type']);
        $this->assertEquals('aes-256-gcm', $config['method']);
    }

    public function test_singbox_build_vmess()
    {
        $server = Server::factory()->make([
            'type' => 'vmess',
            'name' => 'VMess-Server',
            'host' => 'vmess.example.com',
            'port' => 443,
            'password' => 'uuid-1234',
            'protocol_settings' => [
                'tls' => false,
                'network' => 'tcp',
            ],
        ])->toArray();

        $singBox = new SingBox(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], [$server], 'sing-box', '1.8.0');
        $config = $this->invokeMethod($singBox, 'buildVmess', ['uuid-1234', $server]);

        $this->assertEquals('VMess-Server', $config['tag']);
        $this->assertEquals('vmess', $config['type']);
        $this->assertEquals('uuid-1234', $config['uuid']);
    }

    public function test_singbox_build_trojan()
    {
        $server = Server::factory()->make([
            'type' => 'trojan',
            'name' => 'Trojan-Server',
            'host' => 'trojan.example.com',
            'port' => 443,
            'password' => 'trojan-pass',
            'protocol_settings' => [
                'tls' => 1,
            ],
        ])->toArray();

        $singBox = new SingBox(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], [$server], 'sing-box', '1.8.0');
        $config = $this->invokeMethod($singBox, 'buildTrojan', ['trojan-pass', $server]);

        $this->assertEquals('Trojan-Server', $config['tag']);
        $this->assertEquals('trojan', $config['type']);
        $this->assertEquals('trojan-pass', $config['password']);
    }

    public function test_singbox_build_hysteria2()
    {
        $server = Server::factory()->make([
            'type' => 'hysteria',
            'name' => 'Hy2-Server',
            'host' => 'hy2.example.com',
            'port' => 8443,
            'password' => 'hy2-pass',
            'protocol_settings' => [
                'version' => 2,
            ],
        ])->toArray();

        $singBox = new SingBox(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], [$server], 'sing-box', '1.8.0');
        $config = $this->invokeMethod($singBox, 'buildHysteria', ['hy2-pass', $server]);

        $this->assertEquals('Hy2-Server', $config['tag']);
        $this->assertEquals('hysteria2', $config['type']);
        $this->assertEquals('hy2-pass', $config['password']);
    }

    public function test_singbox_build_tuic()
    {
        $server = Server::factory()->make([
            'type' => 'tuic',
            'name' => 'Tuic-Server',
            'host' => 'tuic.example.com',
            'port' => 1443,
            'password' => 'tuic-pass',
            'protocol_settings' => [],
        ])->toArray();

        $singBox = new SingBox(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], [$server], 'sing-box', '1.8.0');
        $config = $this->invokeMethod($singBox, 'buildTuic', ['tuic-pass', $server]);

        $this->assertEquals('Tuic-Server', $config['tag']);
        $this->assertEquals('tuic', $config['type']);
    }

    public function test_singbox_build_socks()
    {
        $server = Server::factory()->make([
            'type' => 'socks',
            'name' => 'Socks-Server',
            'host' => 'socks.example.com',
            'port' => 1080,
            'password' => 'socks-pass',
            'protocol_settings' => [],
        ])->toArray();

        $singBox = new SingBox(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], [$server], 'sing-box', '1.8.0');
        $config = $this->invokeMethod($singBox, 'buildSocks', ['socks-pass', $server]);

        $this->assertEquals('Socks-Server', $config['tag']);
        $this->assertEquals('socks', $config['type']);
        $this->assertEquals('socks-pass', $config['username']);
    }
}
