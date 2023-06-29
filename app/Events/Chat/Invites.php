<?php

namespace App\Events\Chat;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Uuid;

class Invites implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invite;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Friendship $invite, User $user)
    {
        $this->invite = $invite;
        $this->user = $user;
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
        return new Channel('user.invites.' . $this->user->id);
    }

    public function broadcastAs()
    {
        return 'Invites';
    }

    public function broadcastWith()
    {
        return [
            'new_invite' => [
                'id' => $this->invite->id,
                'user' => [
                    'id' => $this->user->id,
                    'nickname' => $this->user->nickname,
                ]
            ]
        ];
    }
}
