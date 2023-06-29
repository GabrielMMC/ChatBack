<?php

namespace App\Http\Controllers;

use App\Events\Chat\SendMessage;
use App\Http\Requests\MessageRequest;
use App\Models\Friendship;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class MessageController extends Controller
{
    public function store_message(MessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = $request->validated();

            $message = new Message();
            $message->fill([
                ...$data,
                'sender' => $user->id,
                'friendship_id' => $request->friendship_id
            ])->save();

            Event::dispatch(new SendMessage($message, $request->reciver, true, ''));

            DB::commit();
            return response()->json([
                'message' => $message,
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao enviar mensagem',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function get_messages($id)
    {
        // return $id;
        try {
            $friendship = Friendship::find($id);
            $messages = $friendship->messages()->orderBy('created_at', 'asc')->paginate(20);

            return response()->json([
                'friend_user' => $friendship->user()->first(),
                'messages' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'total_pages' => $messages->total(),
                    'per_page' => $messages->perPage(),
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao enviar mensagem',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
