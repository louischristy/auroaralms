<?php

namespace App\Notifications;

use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Course $course,
        public ?Certificate $certificate = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Course Completed: ' . $this->course->title)
            ->greeting('Congratulations, ' . $notifiable->name . '!')
            ->line('You have successfully completed:')
            ->line('**' . $this->course->title . '**');

        if ($this->certificate) {
            if ($this->certificate->score !== null) {
                $message->line('**Score:** ' . $this->certificate->score . '%');
            }
            $message->line('Your certificate (No. ' . $this->certificate->certificate_number . ') has been issued.')
                ->action('View Certificates', url('/learn/certificates'));
        } else {
            $message->action('View Course', url('/learn/courses/' . $this->course->id));
        }

        $message->line('Keep up the great work with your security training!');

        return $message;
    }
}
