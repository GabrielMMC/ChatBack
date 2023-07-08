<?php

namespace App\Events\Chat;

use App\Models\Message;
use App\Models\UnseenMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Uuid;

class MessageDisplayed implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $unseen_messages;
  public $receiver;
  public $friendship_id;

  /**
   * Create a new event instance.
   */
  public function __construct(Collection $unseen_messages, string $receiver, string $friendship_id)
  {
    $this->unseen_messages = $unseen_messages;
    $this->receiver = $receiver;
    $this->friendship_id = $friendship_id;
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
    return new Channel('user.unseen_messages.' . $this->receiver);
  }

  public function broadcastAs()
  {
    return 'MessageDisplayed';
  }

  public function broadcastWith()
  {

    return [
      'unseen_messages' => $this->unseen_messages,
      'friendship_id' => $this->friendship_id,
    ];
  }
}
