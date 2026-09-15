<?php

namespace App\Notifications;

use App\Models\PaymentSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentSubmissionReviewedNotification extends Notification
{
    use Queueable;

    public function __construct(protected PaymentSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fee = $this->submission->fee;
        $student = $fee->student;
        $guardianName = $student->guardian?->name ?? $student->guardian_name ?? 'Guardian';

        $message = (new MailMessage)->greeting('Dear ' . $guardianName);

        if ($this->submission->isApproved()) {
            return $message
                ->subject('Payment Proof Approved: ' . $student->full_name)
                ->line('Your submitted proof of payment has been reviewed and approved.')
                ->line('Student: ' . $student->full_name)
                ->line('Amount: ZMW ' . number_format($this->submission->amount, 2))
                ->line('New Balance: ZMW ' . number_format($fee->balance, 2))
                ->action('View Fees', route('parent.fees'))
                ->line('Thank you for your payment.');
        }

        return $message
            ->subject('Payment Proof Needs Attention: ' . $student->full_name)
            ->line('Your submitted proof of payment could not be verified and was not applied to the balance.')
            ->line('Student: ' . $student->full_name)
            ->line('Amount Claimed: ZMW ' . number_format($this->submission->amount, 2))
            ->when($this->submission->review_notes, fn ($m) => $m->line('Reason: ' . $this->submission->review_notes))
            ->action('View Fees', route('parent.fees'))
            ->line('Please check the details and submit again, or contact the school office.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->submission_id,
            'fee_id'        => $this->submission->fee_id,
            'status'        => $this->submission->status,
            'amount'        => $this->submission->amount,
        ];
    }
}
