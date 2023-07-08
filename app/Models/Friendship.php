<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Friendship extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'friendships';
    protected $keyType = "string";

    protected $fillable = [
        'pending',
        'user_id',
        'friend_user_id'
    ];

    public function user()
    {
        $user = auth()->user();
        return $this->belongsTo(User::class, $this->user_id === $user->id ? 'friend_user_id' : 'user_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'friendship_id', 'id');
    }

    public function unseen_messages()
    {
        return $this->hasMany(UnseenMessage::class, 'friendship_id', 'id');
    }
}
