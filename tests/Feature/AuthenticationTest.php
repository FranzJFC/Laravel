<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\Task;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function profile(User $user): Member
    {
        $category = MembershipCategory::firstOrCreate(['slug' => 'regular'], ['name' => 'Regular', 'fee' => 500, 'is_active' => true]);
        $level = MembershipLevel::firstOrCreate(['slug' => 'basic'], ['name' => 'Basic', 'fee' => 500, 'validity_months' => 12, 'is_active' => true]);

        return Member::create([
            'user_id' => $user->id, 'member_number' => 'MEM-'.Str::random(12),
            'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
            'first_name' => $user->name, 'last_name' => 'Member', 'email' => $user->email,
            'joined_at' => today(), 'status' => 'pending',
        ]);
    }

    private function signupData(): array
    {
        $category = MembershipCategory::create(['name' => 'Student', 'slug' => 'student', 'fee' => 250, 'is_active' => true]);
        $level = MembershipLevel::create(['name' => 'Silver', 'slug' => 'silver', 'fee' => 1000, 'validity_months' => 12, 'is_active' => true]);

        return [
            'full_name' => 'Ana Cruz', 'email' => 'ana@example.test',
            'password' => 'SafePassword123', 'password_confirmation' => 'SafePassword123',
            'membership_category_id' => $category->id, 'membership_level_id' => $level->id,
            'latitude' => 14.5995, 'longitude' => 120.9842,
        ];
    }

    public function test_guests_have_separate_logins_and_signup_without_admin_navigation(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/login')->assertOk()->assertSee(route('admin.login'), false)->assertSee(route('member.login'), false);
        $this->get('/admin/login')->assertOk()->assertSee('Admin login');
        $this->get('/member/login')->assertOk()->assertSee('Sign up and choose a membership');
        $this->get('/signup')->assertOk()->assertSee('Create your membership.')
            ->assertSee('membership_level_id', false)->assertSee('password_confirmation', false)
            ->assertSee('leaflet@1.9.4')->assertDontSee('Membership navigation');
        $this->get('/membership/apply')->assertRedirect(route('register'));
    }

    public function test_guests_cannot_read_private_pages_or_submit_admin_changes(): void
    {
        $this->get('/membership')->assertRedirect(route('admin.login'));
        $this->get('/membership/members')->assertRedirect(route('admin.login'));
        foreach (['/member', '/member/transactions', '/member/payments', '/tasks', '/welcome'] as $url) {
            $this->getJson($url)->assertUnauthorized();
        }
        foreach (['/tasks', '/membership/members', '/membership/categories', '/membership/levels', '/membership/transactions'] as $url) {
            $this->postJson($url, [])->assertUnauthorized();
        }
    }

    public function test_admin_login_redirects_to_admin_overview_and_regenerates_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->get('/admin/login');
        $sessionId = session()->getId();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('membership.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->assertNotSame($sessionId, session()->getId());
        $this->get('/membership')->assertOk()->assertSee('Overview')->assertSee('Categories')->assertDontSee('>Apply</a>', false);
        $this->get('/signup')->assertRedirect(route('membership.dashboard'));
    }

    public function test_member_login_redirects_to_readonly_portal(): void
    {
        $user = User::factory()->create();
        $this->profile($user);
        $this->post('/member/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/member')->assertOk()->assertSee('My payments')->assertDontSee('Configuration')->assertDontSee('Membership levels');
    }

    public function test_credentials_cannot_be_used_on_the_wrong_login_even_with_forged_role(): void
    {
        $member = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->post('/admin/login', ['email' => $member->email, 'password' => 'password', 'role' => 'member', 'login_role' => 'member'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/member/login', ['email' => $admin->email, 'password' => 'password', 'role' => 'admin', 'login_role' => 'admin'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_failed_login_attempts_are_limited(): void
    {
        $user = User::factory()->create();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/member/login', ['email' => $user->email, 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }
        $this->post('/member/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_members_are_blocked_from_all_management_pages_and_mutations(): void
    {
        $user = User::factory()->create();
        $member = $this->profile($user);
        $transaction = MembershipTransaction::create([
            'member_id' => $member->id, 'reference_number' => 'LOCKED-REFERENCE', 'type' => 'payment',
            'amount' => 500, 'status' => 'pending', 'transaction_date' => today(),
        ]);
        $this->actingAs($user);
        foreach (['/membership', '/membership/members', '/membership/members/create', '/membership/members/'.$member->id,
            '/membership/members/'.$member->id.'/edit', '/membership/categories', '/membership/levels',
            '/membership/transactions', '/membership/transactions/create', '/welcome', '/about',
        ] as $url) {
            $this->get($url)->assertForbidden();
        }
        foreach (['/tasks', '/membership/members', '/membership/categories', '/membership/levels', '/membership/transactions'] as $url) {
            $this->postJson($url, [])->assertForbidden();
        }
        $this->putJson('/membership/members/'.$member->id, [])->assertForbidden();
        $this->deleteJson('/membership/members/'.$member->id)->assertForbidden();
        $this->deleteJson('/membership/members/'.$member->id.'/permanent')->assertForbidden();
        $this->patchJson('/membership/members/'.$member->id.'/activate')->assertForbidden();
        $this->putJson('/membership/categories/'.$member->membership_category_id, [])->assertForbidden();
        $this->deleteJson('/membership/categories/'.$member->membership_category_id)->assertForbidden();
        $this->putJson('/membership/levels/'.$member->membership_level_id, [])->assertForbidden();
        $this->deleteJson('/membership/levels/'.$member->membership_level_id)->assertForbidden();
        $this->patchJson('/membership/transactions/'.$transaction->id.'/status', ['status' => 'completed'])->assertForbidden();
        $this->assertSame('pending', $transaction->fresh()->status);
        $this->assertDatabaseHas('members', ['id' => $member->id, 'status' => 'pending']);
    }

    public function test_member_only_sees_own_transactions_and_payments_even_with_forged_filters(): void
    {
        $user = User::factory()->create();
        $member = $this->profile($user);
        $other = $this->profile(User::factory()->create());
        foreach ([[$member, 'MY-PAYMENT', 'payment'], [$member, 'MY-APPLICATION', 'application'], [$other, 'PRIVATE-PAYMENT', 'payment']] as [$profile, $ref, $type]) {
            MembershipTransaction::create(['member_id' => $profile->id, 'reference_number' => $ref, 'type' => $type, 'amount' => 500, 'status' => 'pending', 'transaction_date' => today()]);
        }
        $this->actingAs($user);
        $this->get('/member/transactions?member_id='.$other->id)->assertOk()
            ->assertSee('MY-PAYMENT')->assertSee('MY-APPLICATION')->assertDontSee('PRIVATE-PAYMENT')
            ->assertDontSee('Record transaction')->assertDontSee('name="status"', false);
        $this->get('/member/payments?member_id='.$other->id)->assertOk()
            ->assertSee('MY-PAYMENT')->assertDontSee('MY-APPLICATION')->assertDontSee('PRIVATE-PAYMENT');
        Task::create(['task_title' => 'A shared task', 'description' => 'Read this update']);
        $this->get('/tasks')->assertOk()->assertSee('A shared task')->assertDontSee('Add a new task')->assertDontSee('Add Task');
    }

    public function test_unlinked_accounts_cannot_see_another_members_records(): void
    {
        $other = $this->profile(User::factory()->create());
        MembershipTransaction::create(['member_id' => $other->id, 'reference_number' => 'PRIVATE-RECORD', 'type' => 'payment', 'amount' => 100, 'status' => 'completed', 'transaction_date' => today()]);
        $this->actingAs(User::factory()->create());
        $this->get('/member/transactions')->assertOk()->assertDontSee('PRIVATE-RECORD');
        $this->get('/member')->assertOk()->assertSee('not linked');
    }

    public function test_signup_creates_member_account_and_pending_records_using_server_side_fee(): void
    {
        Notification::fake();
        $data = $this->signupData();
        $this->post('/signup', [...$data, 'role' => 'admin', 'status' => 'active', 'amount' => 1, 'user_id' => 999])
            ->assertRedirect(route('member.dashboard'));
        $user = User::where('email', 'ana@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('member', $user->role);
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $member = $user->member;
        $this->assertSame('pending', $member->status);
        $this->assertSame((int) $data['membership_level_id'], $member->membership_level_id);
        $this->assertNull($member->expires_at);
        $this->assertDatabaseHas('membership_transactions', ['member_id' => $member->id, 'amount' => 1000, 'status' => 'pending', 'type' => 'application']);
        $application = MembershipApplication::where('user_id', $user->id)->firstOrFail();
        $this->assertSame((int) $data['membership_level_id'], $application->membership_level_id);
        $this->assertArrayNotHasKey('password', $application->getAttributes());
        Notification::assertSentOnDemand(MembershipApplicationSubmitted::class);
        $this->get('/member')->assertOk()->assertSee('Silver')->assertSee($application->reference_no);
    }

    public function test_signup_rejects_inactive_memberships_and_weak_or_mismatched_passwords(): void
    {
        Notification::fake();
        $data = $this->signupData();
        MembershipCategory::findOrFail($data['membership_category_id'])->update(['is_active' => false]);
        MembershipLevel::findOrFail($data['membership_level_id'])->update(['is_active' => false]);
        $this->get('/signup')->assertOk()->assertDontSee('Silver')->assertDontSee('Student (');
        $this->postJson('/signup', [...$data, 'password' => 'short', 'password_confirmation' => 'different'])
            ->assertUnprocessable()->assertJsonValidationErrors(['membership_category_id', 'membership_level_id', 'password']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('members', 0);
        $this->assertDatabaseCount('membership_applications', 0);
        $this->assertDatabaseCount('membership_transactions', 0);
        Notification::assertNothingSent();
    }

    public function test_admin_can_approve_a_signup_without_marking_payment_as_completed(): void
    {
        Notification::fake();
        $this->post('/signup', $this->signupData())->assertRedirect(route('member.dashboard'));
        $user = User::where('email', 'ana@example.test')->firstOrFail();
        $member = $user->member;
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('members.show', $member))->assertOk()->assertSee('Approve membership');
        $this->patch(route('members.activate', $member))->assertRedirect();
        $this->assertSame('active', $member->fresh()->status);
        $this->assertSame(today()->addMonths(12)->toDateString(), $member->fresh()->expires_at->toDateString());
        $this->assertDatabaseHas('membership_applications', ['user_id' => $user->id, 'status' => 'approved']);
        $this->assertDatabaseHas('membership_transactions', ['member_id' => $member->id, 'status' => 'pending']);
    }

    public function test_signup_cannot_claim_existing_member_history_by_email(): void
    {
        $data = $this->signupData();
        $existing = $this->profile(User::factory()->create(['email' => 'existing@example.test']));
        $existing->update(['user_id' => null]);
        $this->postJson('/signup', [...$data, 'email' => 'EXISTING@example.test'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertNull($existing->fresh()->user_id);
        $this->assertGuest();
    }

    public function test_logout_ends_session_and_is_post_only(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/logout')->assertStatus(405);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/member')->assertRedirect(route('login'));
    }

    public function test_admin_can_be_created_only_with_local_password_prompt(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Admin name', 'Site Admin')
            ->expectsQuestion('Admin email', 'admin@example.test')
            ->expectsQuestion('Password (at least 8 characters, with letters and numbers)', 'AdminPassword123')
            ->expectsQuestion('Confirm password', 'AdminPassword123')
            ->assertSuccessful();
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('AdminPassword123', $admin->password));
    }

    public function test_existing_member_can_get_a_login_without_losing_transactions(): void
    {
        $profile = $this->profile(User::factory()->create(['email' => 'previous@example.test']));
        $profile->update(['user_id' => null, 'email' => 'existing@example.test']);
        $this->artisan('app:create-member-login', ['member' => $profile->id])
            ->expectsQuestion('Member email', 'existing@example.test')
            ->expectsQuestion('Password (at least 8 characters, with letters and numbers)', 'MemberPassword123')
            ->expectsQuestion('Confirm password', 'MemberPassword123')
            ->assertSuccessful();
        $user = $profile->fresh()->user;
        $this->assertSame('member', $user->role);
        $this->assertTrue(Hash::check('MemberPassword123', $user->password));
    }
}
