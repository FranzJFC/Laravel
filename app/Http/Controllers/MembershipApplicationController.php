<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\MembershipApplication;
use App\Models\MembershipCategory;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MembershipApplicationController extends Controller
{
    public function create(): View
    {
        return view('membership.create', ['categories' => MembershipCategory::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(StoreMembershipApplicationRequest $request): RedirectResponse
    {
        $application = DB::transaction(function () use ($request) {
            $application = MembershipApplication::create([...$request->validated(), 'reference_no' => 'MEM-'.Str::upper(Str::random(10))]);
            Notification::route('mail', $application->email)->notify(new MembershipApplicationSubmitted($application));
            $application->update(['verification_sent_at' => now()]);

            return $application;
        });

        return redirect()->route('membership.create')->with('success', "Application saved. Verification email queued. Reference: {$application->reference_no}");
    }
}
