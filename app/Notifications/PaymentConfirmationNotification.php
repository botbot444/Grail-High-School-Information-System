<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PaymentConfirmationNotification extends Notification
{
    use Queueable;

    public function __construct(protected Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fee = $this->payment->fee;
        $student = $fee->student;
        $guardianName = $student->guardian?->name ?? $student->guardian_name ?? 'Guardian';

        return (new MailMessage)
            ->subject('Payment Confirmation: ' . $student->full_name)
            ->greeting('Dear ' . $guardianName)
            ->line('We have received a payment for the following fee:')
            ->line('Student: ' . $student->full_name)
            ->line('Amount Paid: ZMW ' . number_format($this->payment->amount, 2))
            ->line('Payment Method: ' . $this->payment->method_label)
            ->line('Payment Date: ' . $this->payment->payment_date->format('d M Y'))
            ->line('New Balance: ZMW ' . number_format($fee->balance, 2))
            ->line('Status: ' . $fee->status)
            ->action('View Fee Details', route('admin.fees.show', $fee->fee_id))
            ->line('Thank you for your payment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->payment_id,
            'fee_id'     => $this->payment->fee_id,
            'amount'     => $this->payment->amount,
            'method'     => $this->payment->payment_method,
        ];
    }
}
