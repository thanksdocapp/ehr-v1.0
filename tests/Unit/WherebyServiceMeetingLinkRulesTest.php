<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\WherebyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WherebyServiceMeetingLinkRulesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function whereby_enabled_does_not_require_manual_link_for_whereby_platform(): void
    {
        Setting::updateOrCreate(['key' => 'whereby_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'whereby_api_key'], ['value' => 'test-api-key']);

        $service = app(WherebyService::class);

        $this->assertFalse($service->requiresManualMeetingLink('whereby'));
        $this->assertSame('whereby', $service->resolvedOnlineMeetingPlatform(null));
    }

    /** @test */
    public function zoom_still_requires_manual_meeting_link_when_whereby_enabled(): void
    {
        Setting::updateOrCreate(['key' => 'whereby_enabled'], ['value' => '1']);
        Setting::updateOrCreate(['key' => 'whereby_api_key'], ['value' => 'test-api-key']);

        $service = app(WherebyService::class);

        $this->assertTrue($service->requiresManualMeetingLink('zoom'));
    }
}
