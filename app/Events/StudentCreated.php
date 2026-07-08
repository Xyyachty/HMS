<?php

namespace App\Events;

use App\Models\Student;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public Student $student)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('students');
    }

    public function broadcastAs(): string
    {
        return 'StudentCreated';
    }

    public function broadcastWith(): array
    {
        $displayName = trim(implode(' ', array_filter([
            $this->user->last_name ?? null,
            $this->user->first_name ?? null,
            $this->user->middle_name ?? null,
        ])));

        return [
            'user_id' => $this->user->id,
            'student_id' => $this->student->student_id,
            'name' => $displayName !== '' ? $displayName : ($this->user->name ?? 'Student'),
            'email' => $this->user->email,
            'phone_number' => $this->user->phone_number,
            'status' => $this->user->status ?? 'pending',
            'joined' => optional($this->user->created_at)->format('M d, Y'),
        ];
    }
}
