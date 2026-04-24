<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EvaluationL3Assignment;
use App\Models\User;

class AssignmentSubmitted extends Notification
{
    use Queueable;

    public $assignment;
    public $student;

    /**
     * Create a new notification instance.
     */
    public function __construct(EvaluationL3Assignment $assignment)
    {
        $this->assignment = $assignment;
        $this->student = $assignment->enrollment->user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'student_name' => $this->student->name,
            'course_title' => $this->assignment->enrollment->course->title,
            'message' => "{$this->student->name} has submitted an assignment for {$this->assignment->enrollment->course->title}."
        ];
    }
}
