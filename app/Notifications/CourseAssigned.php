<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Course $course,
        public CourseEnrollment $enrollment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('New Course Assigned: ' . $this->course->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned a new training course:')
            ->line('**' . $this->course->title . '**')
            ->line($this->course->description ?? '');

        if ($this->enrollment->due_date) {
            $message->line('**Due date:** ' . $this->enrollment->due_date->format('M j, Y'));
        }

        $message->action('Start Course', url('/learn/courses/' . $this->course->id))
            ->line('Complete this course to earn your certificate.');

        return $message;
    }
}
