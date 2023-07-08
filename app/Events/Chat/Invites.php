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
    public $new_friend_user;

    /**
     * Create a new event instance.
     */
    public function __construct(Friendship $invite, User $user, string $new_friend_user)
    {
        $this->invite = $invite;
        $this->user = $user;
        $this->new_friend_user = $new_friend_user;
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
        if ($this->new_friend_user) {
            return new Channel('user.invites.' . $this->new_friend_user);
        } else {
            return new Channel('user.invites.' . $this->user->id);
        }
    }

    public function broadcastAs()
    {
        return 'Invites';
    }

    public function broadcastWith()
    {
        if ($this->new_friend_user) {
            return [
                'type' => 'new_friendship',
                'friendship' => [
                    'id' => $this->invite->id,
                    'user' => $this->user,
                    'notification' => 0
                ]
            ];
        }
        return [
            'type' => 'new_invite',
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
