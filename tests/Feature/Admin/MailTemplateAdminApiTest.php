<?php

namespace Tests\Feature\Admin;

use App\Models\MailTemplate;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MailTemplateAdminApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
    }

    protected function tearDown(): void
    {
        try {
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_list_mail_templates()
    {
        $this->json('GET', $this->getAdminUri('mail/template/list'))
             ->assertStatus(200)
             ->assertJsonStructure(['data' => [['name', 'label', 'customized']]]);
    }

    public function test_get_mail_template()
    {
        $this->json('GET', $this->getAdminUri('mail/template/get'), ['name' => 'notify'])
             ->assertStatus(200)
             ->assertJsonPath('data.name', 'notify')
             ->assertJsonStructure(['data' => ['subject', 'content']]);
    }

    public function test_save_mail_template()
    {
        $this->json('POST', $this->getAdminUri('mail/template/save'), [
            'name' => 'notify',
            'subject' => 'Test Subject',
            'content' => 'Hello {{name}}, here is your {{content}}'
        ])->assertStatus(200);

        $this->assertDatabaseHas('v2_mail_templates', ['name' => 'notify', 'subject' => 'Test Subject']);
    }

    public function test_reset_mail_template()
    {
        MailTemplate::create(['name' => 'notify', 'subject' => 'Sub', 'content' => 'Content']);
        
        $this->json('POST', $this->getAdminUri('mail/template/reset'), ['name' => 'notify'])
             ->assertStatus(200);

        $this->assertDatabaseMissing('v2_mail_templates', ['name' => 'notify']);
    }

    public function test_test_send_mail()
    {
        \Illuminate\Support\Facades\Mail::fake();
        
        $this->json('POST', $this->getAdminUri('mail/template/test'), ['name' => 'notify'])
             ->assertStatus(200);
    }
}
