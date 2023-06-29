<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuid;

    protected $table = 'users';
    protected $keyType = "string";

    protected $fillable = [
        'nickname',
        'email',
        'password',
        'online',
        'last_online_date',
        'file',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id', 'id');
    }
}
