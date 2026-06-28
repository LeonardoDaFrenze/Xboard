<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MailService;

class MailServiceTest extends TestCase
{
    public function test_mail_service_methods_exist()
    {
        $service = new MailService();

        $this->assertTrue(method_exists($service, 'getTotalUsersNeedRemind'));
        $this->assertTrue(method_exists($service, 'processUsersInChunks'));
        $this->assertTrue(method_exists($service, 'remindTraffic'));
    }
}
