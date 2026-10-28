<?php

use App\Models\Member;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = MembershipCategory::create([
        'name' => 'Regular', 'slug' => 'regular', 'is_active' => true,
    ]);
    $this->level = MembershipLevel::create([
        'name' => 'Gold', 'slug' => 'gold', 'fee' => 2000,
        'validity_months' => 12, 'benefits' => 'Priority access', 'is_active' => true,
    ]);
});

test('a member can be registered with an application transaction', function () {
    $response = $this->post(route('members.store'), [
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'email' => 'maria@example.com',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'joined_at' => '2026-10-28',
        'status' => 'active',
    ]);

    $member = Member::firstOrFail();
    $response->assertRedirect(route('members.show', $member));
    expect($member->member_number)->toStartWith('MEM-2026-')
        ->and($member->expires_at->format('Y-m-d'))->toBe('2027-10-28');
    $this->assertDatabaseHas('membership_transactions', [
        'member_id' => $member->id,
        'type' => 'application',
        'amount' => 2000,
        'status' => 'completed',
    ]);
});

test('members can be searched by category or level name', function () {
    $member = Member::create([
        'member_number' => 'MEM-2026-ABC123',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'first_name' => 'Jose', 'last_name' => 'Rizal',
        'joined_at' => '2026-01-01', 'expires_at' => '2027-01-01', 'status' => 'active',
    ]);

    $this->get(route('members.index', ['search' => 'Gold']))
        ->assertOk()->assertSee($member->full_name);
    $this->get(route('members.index', ['search' => 'Regular']))
        ->assertOk()->assertSee($member->full_name);
});

test('deactivation retains the member record', function () {
    $member = Member::create([
        'member_number' => 'MEM-2026-DEF456',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'first_name' => 'Andres', 'last_name' => 'Bonifacio',
        'joined_at' => '2026-01-01', 'status' => 'active',
    ]);

    $this->delete(route('members.destroy', $member))->assertRedirect(route('members.index'));
    expect($member->fresh()->status)->toBe('inactive');
    $this->assertDatabaseHas('members', ['id' => $member->id]);
});

test('completing a renewal extends membership validity once', function () {
    $member = Member::create([
        'member_number' => 'MEM-2026-GHI789',
        'membership_category_id' => $this->category->id,
        'membership_level_id' => $this->level->id,
        'first_name' => 'Gabriela', 'last_name' => 'Silang',
        'joined_at' => '2026-01-01', 'expires_at' => '2027-01-01', 'status' => 'active',
    ]);
    $transaction = MembershipTransaction::create([
        'reference_number' => 'TXN-20261028-TEST0001', 'member_id' => $member->id,
        'type' => 'renewal', 'amount' => 2000, 'status' => 'pending',
        'transaction_date' => '2026-10-28',
    ]);

    $this->patch(route('transactions.status', $transaction), ['status' => 'completed'])->assertRedirect();
    expect($member->fresh()->expires_at->format('Y-m-d'))->toBe('2028-01-01');

    $this->patch(route('transactions.status', $transaction), ['status' => 'completed'])->assertRedirect();
    expect($member->fresh()->expires_at->format('Y-m-d'))->toBe('2028-01-01');
});

test('membership administration pages render', function () {
    $this->get(route('membership.dashboard'))
        ->assertOk()
        ->assertSee('Good to see you.')
        ->assertSee('Tasks')
        ->assertSee(route('tasks.index'), false);
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Task management')
        ->assertSee('Membership management')
        ->assertSee(route('membership.dashboard'), false);
    $this->get(route('members.create'))->assertOk()->assertSee('Register a new member');
    $this->get(route('categories.index'))->assertOk()->assertSee('Membership categories');
    $this->get(route('levels.index'))->assertOk()->assertSee('Membership levels');
    $this->get(route('transactions.index'))->assertOk()->assertSee('Transactions');
    $this->get(route('transactions.create'))->assertOk()->assertSee('Record a transaction');
});
