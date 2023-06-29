<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class SelectService
{
    public static function selectInvites($invites): Collection
    {
        return collect($invites->items())->map(function ($item) {
            return [
                'id' => $item['id'],
                'user' => [
                    'id' => $item['user']['id'],
                    'nickname' => $item['user']['nickname']
                ]
            ];
        });
    }
}
