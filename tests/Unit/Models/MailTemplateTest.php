<?php

namespace Tests\Unit\Models;

use App\Models\MailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a mail template can be created successfully.
     *
     * @return void
     */
    public function test_mail_template_creation_is_successful(): void
    {
        $template = new MailTemplate();
        $template->name = 'Welcome Email';
        $template->subject = 'Welcome to our service!';
        $template->content = '<p>Hello, welcome!</p>';
        $template->save();

        $this->assertModelExists($template);

        $retrieved = MailTemplate::find($template->id);
        $this->assertEquals('Welcome Email', $retrieved->name);
        $this->assertEquals('Welcome to our service!', $retrieved->subject);
    }

    /**
     * Test that a mail template can be updated successfully.
     *
     * @return void
     */
    public function test_mail_template_can_be_updated(): void
    {
        $template = new MailTemplate();
        $template->name = 'Password Reset';
        $template->subject = 'Reset your password';
        $template->content = 'Reset link here';
        $template->save();

        $template->subject = 'Important: Reset your password';
        $template->save();

        $this->assertEquals('Important: Reset your password', $template->fresh()->subject);
    }

    /**
     * Test that a mail template can be deleted.
     *
     * @return void
     */
    public function test_mail_template_can_be_deleted(): void
    {
        $template = new MailTemplate();
        $template->name = 'Notification';
        $template->subject = 'Notice';
        $template->content = 'Some notice content';
        $template->save();

        $template->delete();

        $this->assertModelMissing($template);
    }
}
