<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use App\Models\User;
use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('member.dashboard')->with('success', 'Your email address is already verified.');
        }

        return view('auth.verify-email', [
            'application' => MembershipApplication::where('user_id', $request->user()->id)
                ->where('email', $request->user()->email)->latest()->first(),
        ]);
    }

    public function verify(Request $request, string $id, string $hash): View|RedirectResponse|Response
    {
        // A valid signed link proves inbox access; it never signs the visitor in.
        $user = User::findOrFail($id);
        if ($request->user()?->id === $user->id) {
            $user = $request->user()->refresh();
        }
        if ($user->role !== 'member' || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->view('auth.verification-invalid', [], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if ($request->user()?->id === $user->id) {
            return redirect()->route('member.dashboard')->with('success', 'Your email address has been verified. Membership approval is handled separately by an administrator.');
        }

        return view('auth.email-verified');
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('success', 'Your email address is already verified.');
        }

        $application = MembershipApplication::where('user_id', $request->user()->id)
            ->where('email', $request->user()->email)->latest()->first();
        if (! $application) {
            return back()->with('error', 'No matching signup application was found. Please contact an administrator.');
        }

        Notification::route('mail', $request->user()->email)->notify(new MembershipApplicationSubmitted($application));
        $application->update(['verification_sent_at' => now()]);

        return back()->with('success', 'A new verification email has been queued. Check your inbox and spam folder.');
    }
}
