<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ExternalNotificationDisabledTest extends TestCase
{
    public function test_whatsapp_notification_permanently_disabled_and_does_not_hit_external_api(): void
    {
        Http::fake();

        $service = new NotificationService();
        $result = $service->sendWhatsApp('081234567890', 'Pesan Uji');

        $this->assertTrue($result['success']);
        $this->assertEquals('simulated', $result['mode']);
        Http::assertNothingSent();
    }

    public function test_email_notification_permanently_disabled_and_does_not_send_mail(): void
    {
        Mail::fake();

        $service = new NotificationService();
        $result = $service->sendEmail('siswa@example.com', 'Subject Uji', 'Body Uji');

        $this->assertTrue($result['success']);
        $this->assertEquals('disabled', $result['mode']);
        Mail::assertNothingSent();
    }

    public function test_whatsapp_token_always_returns_null(): void
    {
        $service = new NotificationService();
        $this->assertNull($service->getWhatsAppToken());
    }
}
