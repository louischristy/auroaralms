<?php

namespace App\Notifications;

use App\Models\Policy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PolicyPushed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Policy $policy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Required: New Policy — ' . $this->policy->title)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('A new policy has been published that requires your acknowledgment:')
            ->line('**' . $this->policy->title . '**')
            ->line($this->policy->description ?? '')
            ->action('Review & Acknowledge', url('/learn/policies/' . $this->policy->id))
            ->line('Please review and acknowledge this policy at your earliest convenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'policy_pushed',
            'policy_id' => $this->policy->id,
            'policy_title' => $this->policy->title,
        ];
    }
}
