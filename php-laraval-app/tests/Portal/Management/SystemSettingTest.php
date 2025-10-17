<?php

namespace Tests\Portal\Management;

use App\Enums\GlobalSetting\GlobalSettingEnum;
use App\Enums\SettingTypeEnum;
use App\Jobs\BroadcastNotificationJob;
use App\Models\GlobalSetting;
use Bus;
use Inertia\Testing\AssertableInertia as Assert;
use Str;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    public function testAdminCanAccessSystemSettings(): void
    {
        $settings = GlobalSetting::all();

        $this->actingAs($this->admin)
            ->get('/system-settings')
            ->assertInertia(fn (Assert $page) => $page
                ->component('SystemSetting/index')
                ->has('settings', $settings->count())
                ->has('settings.0', fn (Assert $page) => $page
                    ->has('id')
                    ->has('key')
                    ->has('value')
                    ->has('type')
                    ->has('description')
                    ->etc()));
    }

    public function testWorkerCanNotAccessSystemSettings(): void
    {
        $this->actingAs($this->worker)
            ->get('/system-settings')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error/index')
                ->where('code', '404'));
    }

    public function testCanUpdateSystemSettingsAndNotSendBroadcast(): void
    {
        $key = strtoupper(Str::snake(GlobalSettingEnum::InvoiceDueDays()));
        $setting = GlobalSetting::where('key', $key)->first();
        $data = [
            'key' => $setting->key,
            'value' => $setting->type === SettingTypeEnum::Integer() ? 1 : 'test',
        ];

        $this->actingAs($this->admin)
            ->patch("/system-settings/{$setting->key}", $data)
            ->assertStatus(302)
            ->assertRedirect()
            ->assertSessionHas('success', __('system setting updated successfully'));

        $this->assertDatabaseHas('global_settings', [
            'key' => $data['key'],
            'value' => $data['value'],
        ]);

        Bus::assertNotDispatchedAfterResponse(BroadcastNotificationJob::class);
    }
}
