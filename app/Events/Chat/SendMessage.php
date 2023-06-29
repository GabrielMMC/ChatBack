<?php

namespace App\Events\Chat;

use App\Models\Message;
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
    public $reciver;
    public $online;
    public $last_online_date;

    /**
     * Create a new event instance.
     */
    public function __construct($message, string $reciver, bool $online, string $last_online_date)
    {
        $this->message = $message;
        $this->reciver = $reciver;
        $this->online = $online;
        $this->last_online_date = $last_online_date;
    }

    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel('user' . $this->reciver),
    //     ];
    // }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('user.' . $this->reciver);
    }

    public function broadcastAs()
    {
        return 'SendMessage';
    }

    public function broadcastWith()
    {
        if (!$this->online) {
            return [
                'last_online_date' => $this->last_online_date,
            ];
        } else {
            return [
                'message' => $this->message,
            ];
        }
    }
}
