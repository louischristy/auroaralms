<?php

namespace App\Notifications;

use App\Models\CourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CourseEnrollment $enrollment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->enrollment->course;
        $days = now()->diffInDays($this->enrollment->due_date);

        return (new MailMessage)
            ->subject("Overdue: {$course->title}")
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line("Your training course is **{$days} day(s) overdue**:")
            ->line("**{$course->title}**")
            ->line("**Was due:** {$this->enrollment->due_date->format('M j, Y')}")
            ->line("**Progress:** {$this->enrollment->progress_percent}%")
            ->action('Complete Now', url("/learn/courses/{$course->id}"))
            ->line('Please complete this course as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'training_overdue',
            'course_id' => $this->enrollment->course_id,
            'course_title' => $this->enrollment->course->title,
            'due_date' => $this->enrollment->due_date->toDateString(),
            'days_overdue' => now()->diffInDays($this->enrollment->due_date),
        ];
    }
}
