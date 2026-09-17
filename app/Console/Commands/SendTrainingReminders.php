<?php

namespace App\Console\Commands;

use App\Models\CourseEnrollment;
use App\Notifications\TrainingDueReminder;
use App\Notifications\TrainingOverdue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendTrainingReminders extends Command
{
    protected $signature = 'training:send-reminders';
    protected $description = 'Send due-date reminders and overdue alerts for training courses';

    public function handle(): int
    {
        $this->sendDueReminders();
        $this->sendOverdueAlerts();

        return Command::SUCCESS;
    }

    /**
     * Send reminders for training due in 7 days and 1 day.
     */
    private function sendDueReminders(): void
    {
        $reminderDays = [7, 1];

        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $enrollments = CourseEnrollment::with(['user', 'course'])
                ->whereDate('due_date', $targetDate)
                ->whereNotIn('status', ['completed'])
                ->get();

            $count = 0;
            foreach ($enrollments as $enrollment) {
                if (! $enrollment->user) continue;

                // Avoid duplicate reminders: check if we already sent one today
                $alreadySent = DB::table('notifications')
                    ->where('notifiable_id', $enrollment->user->id)
                    ->where('notifiable_type', get_class($enrollment->user))
                    ->where('type', TrainingDueReminder::class)
                    ->whereDate('created_at', now()->toDateString())
                    ->exists();

                if (! $alreadySent) {
                    $enrollment->user->notify(new TrainingDueReminder($enrollment, $days));
                    $count++;
                }
            }

            if ($count > 0) {
                $this->info("Sent {$count} reminder(s) for training due in {$days} day(s).");
            }
        }
    }

    /**
     * Send weekly overdue alerts (every Monday) for past-due enrollments.
     */
    private function sendOverdueAlerts(): void
    {
        // Only send overdue alerts once per week (Monday)
        if (now()->dayOfWeek !== 1) {
            return;
        }

        $enrollments = CourseEnrollment::with(['user', 'course'])
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['completed'])
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            if (! $enrollment->user) continue;

            $enrollment->user->notify(new TrainingOverdue($enrollment));
            $count++;
        }

        if ($count > 0) {
            $this->info("Sent {$count} overdue training alert(s).");
        }
    }
}
