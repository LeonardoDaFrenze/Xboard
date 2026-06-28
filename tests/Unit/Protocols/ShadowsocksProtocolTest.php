<?php

namespace Tests\Unit\Protocols;

use Tests\TestCase;
use App\Models\Server;
use App\Protocols\Shadowsocks;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShadowsocksProtocolTest extends TestCase
{
    use RefreshDatabase;

    public function test_shadowsocks_sip008_format()
    {
        $server = Server::factory()->make([
            'type' => 'shadowsocks',
            'name' => 'SS-Server',
            'host' => 'ss.example.com',
            'port' => 8388,
            'server_port' => 8388,
            'password' => 'ss-pass',
            'protocol_settings' => ['cipher' => 'aes-256-gcm'],
        ])->toArray();
        $server['id'] = 1;

        $user = [
            'u' => 1000,
            'd' => 2000,
            'transfer_enable' => 1073741824,
        ];

        $config = Shadowsocks::SIP008($server, $user);

        $this->assertEquals(1, $config['id']);
        $this->assertEquals('SS-Server', $config['remarks']);
        $this->assertEquals('ss.example.com', $config['server']);
        $this->assertEquals(8388, $config['server_port']);
        $this->assertEquals('ss-pass', $config['password']);
        $this->assertEquals('aes-256-gcm', $config['method']);
    }

    public function test_shadowsocks_protocol_flags()
    {
        $shadowsocks = new \ReflectionClass(Shadowsocks::class);
        $instance = $shadowsocks->newInstance(['u' => 0, 'd' => 0, 'transfer_enable' => 1, 'expired_at' => time() + 86400], []);
        $this->assertTrue(class_exists(Shadowsocks::class));
    }
}
