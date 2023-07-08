<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnseenMessage extends Model
{
    use HasFactory, Uuid;

    public $timestamps = false;
    protected $table = 'unseen_messages';
    protected $keyType = "string";

    protected $fillable = [
        'friendship_id',
        'message_id'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }
}
