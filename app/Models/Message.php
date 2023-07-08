<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $table = 'messages';
    protected $keyType = "string";

    protected $fillable = [
        'content',
        'type',
        'sender',
        'receiver',
        'friendship_id'
    ];

    public static function boot()
    {
        parent::boot();

        //once created/inserted successfully this method fired
        static::created(function (Message $message) {
            UnseenMessage::create([
                'friendship_id' => $message->friendship_id,
                'message_id' => $message->id,
            ]);
        });
    }
}
