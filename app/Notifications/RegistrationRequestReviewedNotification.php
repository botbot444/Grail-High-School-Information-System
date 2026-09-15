<?php

namespace App\Notifications;

use App\Models\RegistrationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RegistrationRequestReviewedNotification extends Notification
{
    use Queueable;

    /**
     * @param  string|null  $childTemporaryPassword  Only set on approval — the
     *         child's freshly generated one-time login password. Never stored
     *         anywhere in readable form, so it has to be carried here at the
     *         moment it's generated rather than re-derived later.
     */
    public function __construct(
        protected RegistrationRequest $registrationRequest,
        protected ?string $childTemporaryPassword = null
    ) {}

    /**
     * A rejected request has no real User to notify (nothing was ever
     * created), so the controller routes it to a raw email address via
     * Notification::route() — an AnonymousNotifiable, which has no
     * notifications() relation and can't take the 'database' channel.
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable
            ? ['mail']
            : ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->registrationRequest;

        $message = (new MailMessage)->greeting('Dear ' . $request->parent_full_name);

        if ($request->isApproved()) {
            return $message
                ->subject('Registration Approved: ' . $request->child_full_name)
                ->line('Your registration has been reviewed and approved. Welcome to Grail!')
                ->line('Your own account is active — sign in with the email and password you chose at sign-up.')
                ->line('A login has also been created for ' . $request->child_full_name . ':')
                ->line('Login email: ' . $request->child_email)
                ->line('One-time password: ' . $this->childTemporaryPassword)
                ->line('They will be asked to choose their own password the first time they sign in.')
                ->action('Sign In', route('login'))
                ->line('Thank you for registering with us.');
        }

        return $message
            ->subject('Registration Update: ' . $request->child_full_name)
            ->line('We were unable to approve your registration as submitted.')
            ->when($request->review_notes, fn ($m) => $m->line('Reason: ' . $request->review_notes))
            ->action('Register Again', route('register'))
            ->line('You are welcome to submit a new registration, or contact the school office if you have questions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'registration_request_id' => $this->registrationRequest->registration_request_id,
            'status'                  => $this->registrationRequest->status,
            'child_full_name'         => $this->registrationRequest->child_full_name,
        ];
    }
}
