<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function application(): MembershipApplication
    {
        $user = User::factory()->unverified()->create(['name' => 'Ana Cruz']);
        $category = MembershipCategory::firstOrCreate(['slug' => 'regular'], ['name' => 'Regular', 'fee' => 500, 'is_active' => true]);
        $level = MembershipLevel::firstOrCreate(['slug' => 'basic'], ['name' => 'Basic', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);
        $member = Member::create([
            'user_id' => $user->id, 'member_number' => 'MEM-'.$user->id,
            'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
            'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => $user->email,
            'joined_at' => today(), 'status' => 'pending',
        ]);
        MembershipTransaction::create([
            'reference_number' => 'TXN-'.$user->id, 'member_id' => $member->id, 'type' => 'application',
            'amount' => 500, 'status' => 'pending', 'transaction_date' => today(),
        ]);

        return MembershipApplication::create([
            'user_id' => $user->id, 'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
            'reference_no' => 'MEM-REFERENCE-'.$user->id, 'full_name' => 'Ana Cruz', 'email' => $user->email,
        ]);
    }

    private function link(MembershipApplication $application): string
    {
        return (new MembershipApplicationSubmitted($application))->toMail(new AnonymousNotifiable)->actionUrl;
    }

    public function test_email_matches_requested_content_and_contains_a_signed_24_hour_link(): void
    {
        $this->freezeTime();
        $application = $this->application();
        $notification = new MembershipApplicationSubmitted($application);
        $mail = $notification->toMail(new AnonymousNotifiable);
        $this->assertSame('Verify your membership email', $mail->subject);
        $this->assertSame('Hello Ana Cruz,', $mail->greeting);
        $this->assertSame('Verify email address', $mail->actionText);
        $this->assertTrue($notification->afterCommit);
        parse_str(parse_url($mail->actionUrl, PHP_URL_QUERY), $query);
        $this->assertSame(now()->addHours(24)->timestamp, (int) $query['expires']);
        $this->assertNotEmpty($query['signature']);

        $html = (string) $mail->render();
        foreach (['MemberFlow', 'Hello Ana Cruz,', 'We received your membership application.', $application->reference_no,
            'Verify email address', 'This verification link expires in 24 hours.', 'copy and paste', 'Regards,',
        ] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString(htmlspecialchars($mail->actionUrl, ENT_QUOTES, 'UTF-8'), $html);
        $plain = (string) app(Markdown::class)->renderText($mail->markdown, $mail->data());
        $this->assertStringContainsString($mail->actionUrl, $plain);
        $this->assertStringContainsString($application->reference_no, $plain);
    }

    public function test_link_verifies_email_while_logged_out_without_signing_in_or_approving_membership(): void
    {
        Event::fake([Verified::class]);
        $application = $this->application();
        $user = User::findOrFail($application->user_id);
        $this->get($this->link($application))->assertOk()->assertSee('Your email is verified.');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertGuest();
        $this->assertSame('pending', $application->fresh()->status);
        $this->assertSame('pending', $user->member->status);
        $this->assertNull($user->member->expires_at);
        $this->assertDatabaseHas('membership_transactions', ['member_id' => $user->member->id, 'status' => 'pending']);
        Event::assertDispatched(Verified::class, fn ($event) => $event->user->is($user));
    }

    public function test_signed_in_member_returns_to_portal_and_sees_verified_status(): void
    {
        $application = $this->application();
        $user = User::findOrFail($application->user_id);
        $this->actingAs($user)->get('/member')->assertOk()->assertSee('Resend verification email');
        $this->get($this->link($application))->assertRedirect(route('member.dashboard'));
        $this->get('/member')->assertOk()->assertSee('Email verified')->assertDontSee('Resend verification email');
        $this->get('/email/verify')->assertRedirect(route('member.dashboard'));
    }

    public function test_invalid_or_expired_links_do_not_verify_any_account(): void
    {
        $this->freezeTime();
        $application = $this->application();
        $url = $this->link($application);
        $user = User::findOrFail($application->user_id);
        $this->get($url.'&changed=1')->assertForbidden()->assertSee('invalid or has expired');
        $unsigned = strtok($url, '?');
        $this->get($unsigned)->assertForbidden();
        $this->travel(24)->hours();
        $this->travel(1)->seconds();
        $this->get($url)->assertForbidden()->assertSee('invalid or has expired');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_valid_signature_with_wrong_email_hash_is_rejected(): void
    {
        $application = $this->application();
        $url = URL::temporarySignedRoute('verification.verify', now()->addDay(), ['id' => $application->user_id, 'hash' => sha1('other@example.test')]);
        $this->get($url)->assertForbidden();
        $this->assertFalse(User::findOrFail($application->user_id)->hasVerifiedEmail());
    }

    public function test_old_link_cannot_verify_a_changed_account_email(): void
    {
        $application = $this->application();
        $url = $this->link($application);
        $user = User::findOrFail($application->user_id);
        $user->update(['email' => 'changed@example.test']);
        $this->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        // Even a delayed queued message is bound to the original recipient email.
        $this->get($this->link($application))->assertForbidden();
    }

    public function test_reusing_a_valid_link_is_harmless_and_dispatches_verified_event_once(): void
    {
        Event::fake([Verified::class]);
        $application = $this->application();
        $url = $this->link($application);
        $this->get($url)->assertOk();
        $verifiedAt = User::findOrFail($application->user_id)->email_verified_at;
        $this->travel(5)->minutes();
        $this->get($url)->assertOk();
        $this->assertTrue($verifiedAt->equalTo(User::findOrFail($application->user_id)->email_verified_at));
        Event::assertDispatchedTimes(Verified::class, 1);
    }

    public function test_verification_never_switches_an_existing_logged_in_account(): void
    {
        $application = $this->application();
        $other = User::factory()->unverified()->create();
        $this->actingAs($other)->get($this->link($application))->assertOk();
        $this->assertAuthenticatedAs($other);
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
        $this->assertTrue(User::findOrFail($application->user_id)->hasVerifiedEmail());
    }

    public function test_resending_is_authenticated_member_only_and_limited_to_three_requests(): void
    {
        Notification::fake();
        $application = $this->application();
        $this->postJson('/email/verification-notification')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/email/verification-notification')->assertForbidden();
        $user = User::findOrFail($application->user_id);
        $this->actingAs($user)->get('/email/verify')->assertOk()->assertSee($application->reference_no);
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->from('/email/verify')->post('/email/verification-notification', ['email' => 'attacker@example.test', 'user_id' => 999])
                ->assertRedirect('/email/verify')->assertSessionHas('success');
        }
        $this->post('/email/verification-notification')->assertStatus(429);
        Notification::assertSentOnDemandTimes(MembershipApplicationSubmitted::class, 3);
        Notification::assertSentOnDemand(MembershipApplicationSubmitted::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $user->email && $notification->application->user_id === $user->id);
        $this->assertNotNull($application->fresh()->verification_sent_at);
    }

    public function test_verified_users_do_not_receive_resend_notifications(): void
    {
        Notification::fake();
        $application = $this->application();
        $user = User::findOrFail($application->user_id);
        $user->markEmailAsVerified();
        $this->actingAs($user)->post('/email/verification-notification')->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_legacy_application_without_account_receives_a_receipt_without_broken_button(): void
    {
        $application = $this->application();
        $application->update(['user_id' => null]);
        $mail = (new MembershipApplicationSubmitted($application))->toMail(new AnonymousNotifiable);
        $this->assertSame('Membership application received', $mail->subject);
        $this->assertNull($mail->actionUrl);
        $this->assertStringContainsString('pending administrator approval', (string) $mail->render());
    }
}
