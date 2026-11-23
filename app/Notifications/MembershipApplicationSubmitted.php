<?php

namespace App\Notifications;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MembershipApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MembershipApplication $application)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->markdown('mail.membership-verification')
            ->subject('Verify your membership email')
            ->greeting('Hello '.$this->application->full_name.',')
            ->line('We received your membership application.')
            ->line('Reference number: '.$this->application->reference_no);

        // Older applications may predate accounts; they still receive a receipt.
        if (! $this->application->user_id) {
            return $mail->subject('Membership application received')
                ->line('Your application is pending administrator approval.');
        }

        $url = URL::temporarySignedRoute('verification.verify', now()->addHours(24), [
            'id' => $this->application->user_id,
            // Bind to the address that received this message, not a later account email.
            'hash' => sha1($this->application->email),
        ]);

        return $mail->action('Verify email address', $url)
            ->line('This verification link expires in 24 hours.')
            ->line('If you did not create this account, you can ignore this email.');
    }
}
