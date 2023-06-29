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
        'reciver',
        'friendship_id'
    ];
}
