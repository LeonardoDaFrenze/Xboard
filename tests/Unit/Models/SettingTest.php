<?php

namespace Tests\Unit\Models;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a setting can be created and persisted successfully.
     *
     * @return void
     */
    public function test_setting_creation_is_successful(): void
    {
        $setting = new Setting();
        $setting->name = 'app_name';
        $setting->value = 'Xboard Application Test';
        $setting->save();

        $this->assertModelExists($setting);

        $retrievedSetting = Setting::find($setting->id);
        $this->assertNotNull($retrievedSetting);
        $this->assertEquals('app_name', $retrievedSetting->name);
        $this->assertEquals('Xboard Application Test', $retrievedSetting->value);
    }

    /**
     * Test that a setting can be updated successfully.
     *
     * @return void
     */
    public function test_setting_can_be_updated(): void
    {
        $setting = new Setting();
        $setting->name = 'maintenance_mode';
        $setting->value = '0';
        $setting->save();

        $setting->value = '1';
        $setting->save();

        $retrievedSetting = Setting::where('name', 'maintenance_mode')->first();
        $this->assertEquals('1', $retrievedSetting->value);
    }

    /**
     * Test that a setting can be deleted successfully.
     *
     * @return void
     */
    public function test_setting_can_be_deleted(): void
    {
        $setting = new Setting();
        $setting->name = 'temp_setting';
        $setting->value = 'temp_value';
        $setting->save();

        $this->assertModelExists($setting);

        $setting->delete();

        $this->assertModelMissing($setting);
    }
}
