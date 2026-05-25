<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Traits\GlobalTrait;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class GlobalTraitTest extends TestCase
{
    use RefreshDatabase;
    use GlobalTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * getSettings() should return a Setting model instance.
     */
    public function test_get_settings_returns_setting_instance(): void
    {
        Setting::create([
            'data_per_page'      => 15,
            'sales_order_limit'  => 100,
            'mcp_deadline'       => 5,
        ]);

        $setting = $this->getSettings();

        $this->assertInstanceOf(Setting::class, $setting);
    }

    /**
     * getSettings() should cache the result so a second call does not hit the DB again.
     */
    public function test_get_settings_result_is_cached(): void
    {
        Setting::create([
            'data_per_page'      => 15,
            'sales_order_limit'  => 100,
            'mcp_deadline'       => 5,
        ]);

        $this->assertFalse(Cache::has('app_settings'));

        $this->getSettings();

        $this->assertTrue(Cache::has('app_settings'));
    }
}
