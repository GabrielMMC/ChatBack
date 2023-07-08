<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $request = parent::toArray($request);
        $user = $this->user()->first();

        return [
            'id' => $this->id,
            'user' => $user,
            'notification' => $this->unseen_messages()->whereHas('message', function ($query) use ($user) {
                $query->where('sender', $user->id);
            })->count()
        ];
    }
}
