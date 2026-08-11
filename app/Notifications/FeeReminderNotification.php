<?php

namespace App\Notifications;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class FeeReminderNotification extends Notification
{
    use Queueable;

    public function __construct(protected Fee $fee) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->fee->student;
        $guardianName = $student->guardian?->name ?? $student->guardian_name ?? 'Guardian';

        return (new MailMessage)
            ->subject('Fee Reminder: ' . $student->full_name)
            ->greeting('Dear ' . $guardianName)
            ->line('This is a reminder that the following fees are due:')
            ->line('Student: ' . $student->full_name)
            ->line('Amount Due: ZMW ' . number_format($this->fee->amount_due, 2))
            ->line('Due Date: ' . $this->fee->due_date->format('d M Y'))
            ->line('Balance: ZMW ' . number_format($this->fee->balance, 2))
            ->action('View Fee Details', route('admin.fees.show', $this->fee->fee_id))
            ->line('Please make arrangements to clear the balance before the due date.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'fee_id'     => $this->fee->fee_id,
            'student'    => $this->fee->student->full_name,
            'amount_due' => $this->fee->amount_due,
            'due_date'   => $this->fee->due_date->format('Y-m-d'),
        ];
    }
}
