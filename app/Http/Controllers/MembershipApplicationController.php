<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\Member;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
use App\Models\MembershipTransaction;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MembershipApplicationController extends Controller
{
    public function create(): View
    {
        return view('membership.create', [
            'categories' => MembershipCategory::where('is_active', true)->orderBy('name')->get(),
            'levels' => MembershipLevel::where('is_active', true)->orderBy('fee')->get(),
        ]);
    }

    public function store(StoreMembershipApplicationRequest $request): RedirectResponse
    {
        [$user, $application] = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $user = new User(['name' => $data['full_name'], 'email' => $data['email'], 'password' => $data['password']]);
            $user->role = 'member';
            $user->save();
            $level = MembershipLevel::findOrFail($data['membership_level_id']);
            $names = preg_split('/\s+/', trim($data['full_name']), 2);
            $member = Member::create([
                'user_id' => $user->id, 'member_number' => 'MEM-'.now()->format('Y').'-'.Str::upper(Str::random(10)),
                'membership_category_id' => $data['membership_category_id'], 'membership_level_id' => $level->id,
                'first_name' => $names[0], 'last_name' => $names[1] ?? '', 'email' => $data['email'],
                'phone' => $data['phone'] ?? null, 'address' => $data['address'] ?? null,
                'joined_at' => today(), 'status' => 'pending',
            ]);
            $application = MembershipApplication::create([
                ...collect($data)->except('password')->all(), 'user_id' => $user->id,
                'reference_no' => 'MEM-'.Str::upper(Str::random(10)),
            ]);
            MembershipTransaction::create([
                'reference_number' => 'TXN-'.now()->format('Ymd').'-'.Str::upper(Str::random(10)),
                'member_id' => $member->id, 'type' => 'application', 'amount' => $level->fee,
                'status' => 'pending', 'transaction_date' => today(), 'notes' => 'Membership signup: '.$level->name,
            ]);
            Notification::route('mail', $application->email)->notify(new MembershipApplicationSubmitted($application));
            $application->update(['verification_sent_at' => now()]);

            return [$user, $application];
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route('member.dashboard')->with('success', "Account created. Verification email queued; check your inbox and spam folder. Membership approval is pending. Reference: {$application->reference_no}");
    }
}
