<?php

namespace Tests\Unit\Services\Plugin;

use App\Services\Plugin\HookManager;
use Tests\TestCase;

class HookManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        HookManager::reset();
    }

    public function test_call_runs_listeners(): void
    {
        $ran = false;
        HookManager::register('test.event', function () use (&$ran) {
            $ran = true;
        });

        HookManager::call('test.event');

        $this->assertTrue($ran);
    }

    public function test_call_passes_arguments(): void
    {
        $result = null;
        HookManager::register('test.event', function ($arg) use (&$result) {
            $result = $arg;
        });

        HookManager::call('test.event', 'hello');
        $this->assertEquals('hello', $result);
    }

    public function test_filter_modifies_value(): void
    {
        HookManager::registerFilter('test.filter', function ($value) {
            return $value . '_modified';
        });

        $result = HookManager::filter('test.filter', 'original');

        $this->assertEquals('original_modified', $result);
    }

    public function test_filter_priority_order(): void
    {
        $order = [];
        HookManager::registerFilter('test.filter', function ($v) use (&$order) { $order[] = 10; return $v; }, 10);
        HookManager::registerFilter('test.filter', function ($v) use (&$order) { $order[] = 5; return $v; }, 5);

        HookManager::filter('test.filter', 'test');

        $this->assertEquals([5, 10], $order);
    }

    public function test_multiple_listeners_all_run(): void
    {
        $count = 0;
        HookManager::register('multi.event', function () use (&$count) { $count++; });
        HookManager::register('multi.event', function () use (&$count) { $count++; });
        HookManager::register('multi.event', function () use (&$count) { $count++; });

        HookManager::call('multi.event');

        $this->assertEquals(3, $count);
    }
}
