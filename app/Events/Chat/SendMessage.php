<?php

namespace App\Events\Chat;

use App\Models\Message;
use App\Models\UnseenMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Uuid;

class SendMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiver;
    public $user;
    public $is_typing;
    public $typing_function;

    /**
     * Create a new event instance.
     */
    public function __construct($message, string $receiver, bool $is_typing, bool $typing_function)
    {
        $this->user = auth()->user();
        $this->message = $message;
        $this->receiver = $receiver;
        $this->is_typing = $is_typing;
        $this->typing_function = $typing_function;
    }

    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel('user' . $this->receiver),
    //     ];
    // }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('user.' . $this->receiver);
    }

    public function broadcastAs()
    {
        return 'SendMessage';
    }

    public function broadcastWith()
    {
        if ($this->typing_function) {
            return [
                'type' => 'typing',
                'sender_id' => $this->user->id,
                'is_typing' => $this->is_typing,
            ];
        } else {
            return [
                'type' => 'message',
                'message' => $this->message,
                'unseen_messages' => UnseenMessage::where('friendship_id', $this->message->friendship_id)->whereHas('message', function ($query) {
                    $query->where('sender', $this->user->id);
                })->count(),
            ];
        }
    }
}
