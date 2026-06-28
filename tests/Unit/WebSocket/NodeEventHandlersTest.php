<?php

namespace Tests\Unit\WebSocket;

use Tests\TestCase;
use App\WebSocket\NodeEventHandlers;

class NodeEventHandlersTest extends TestCase
{
    public function test_node_event_handlers_has_required_methods()
    {
        $this->assertTrue(method_exists(NodeEventHandlers::class, 'handlePong'));
        $this->assertTrue(method_exists(NodeEventHandlers::class, 'handleNodeStatus'));
        $this->assertTrue(method_exists(NodeEventHandlers::class, 'handleDeviceReport'));
    }
}
