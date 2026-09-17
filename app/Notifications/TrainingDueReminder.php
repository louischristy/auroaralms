<?php

namespace App\Notifications;

use App\Models\CourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingDueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CourseEnrollment $enrollment,
        public int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->enrollment->course;
        $due = $this->enrollment->due_date->format('M j, Y');

        $message = (new MailMessage)
            ->subject("Reminder: {$course->title} due in {$this->daysRemaining} day(s)")
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line("This is a reminder that your training course is due soon:")
            ->line("**{$course->title}**")
            ->line("**Due date:** {$due}")
            ->line("**Progress:** {$this->enrollment->progress_percent}%");

        if ($this->enrollment->progress_percent === 0) {
            $message->action('Start Course', url("/learn/courses/{$course->id}"));
        } else {
            $message->action('Continue Course', url("/learn/courses/{$course->id}"));
        }

        $message->line('Please complete this course before the due date.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'training_due_reminder',
            'course_id' => $this->enrollment->course_id,
            'course_title' => $this->enrollment->course->title,
            'due_date' => $this->enrollment->due_date->toDateString(),
            'days_remaining' => $this->daysRemaining,
            'progress' => $this->enrollment->progress_percent,
        ];
    }
}
