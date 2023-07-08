<?php

namespace App\Events\Chat;

use App\Models\Friendship;
use DateTime;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Status implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $receiver_id;
  public $auth_user;
  public $is_online;
  public $last_online_date;

  /**
   * Create a new event instance.
   */
  public function __construct(string $receiver_id, bool $is_online, bool $is_typing, $last_online_date)
  {
    $this->auth_user =  auth()->user();
    $this->receiver_id = $receiver_id;
    $this->is_online = $is_online;
    $this->last_online_date = $last_online_date;
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn()
  {
    return new Channel('user.status.' . $this->receiver_id);
  }

  public function broadcastAs()
  {
    return 'Status';
  }

  public function broadcastWith()
  {
    return [
      'sender_id' => $this->auth_user->id,
      'is_online' => $this->is_online,
      'last_online_date' => $this->last_online_date,
    ];
  }
}
