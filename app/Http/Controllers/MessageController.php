<?php

namespace App\Http\Controllers;

use App\Events\Chat\MessageDisplayed;
use App\Events\Chat\SendMessage;
use App\Http\Requests\MessageRequest;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\UnseenMessage;
use Illuminate\Database\Eloquent\Collection;
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

            Event::dispatch(new SendMessage($message, $request->receiver, false, false));

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

    public function store_image_message(MessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $data = $request->validated();
            $messages = [];

            foreach ($request->images as $image) {
                // Defina o tamanho máximo permitido para a imagem em bytes
                $size = $image->getSize();
                // Verifique se o tamanho da imagem é menor ou igual ao tamanho máximo permitido
                if ($size > 1024 * (1024 * 5)) {
                    throw new \Exception('O tamanho da imagem é muito grande');
                }

                $message = new Message();
                $message->fill([
                    ...$data,
                    'sender' => $user->id,
                    'friendship_id' => $request->friendship_id
                ]);

                $name = uniqid('img_') . '.' .   $image->getClientOriginalExtension();
                $image->storeAs('message_images', $name, ['disk' => 'public']);
                $message->content = 'message_images/' . $name;
                $message->save();

                $messages[] = $message;
                Event::dispatch(new SendMessage($message, $request->receiver, false, false));
            }


            DB::commit();
            return response()->json([
                'messages' => $messages,
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

    public function store_audio_message(MessageRequest $request)
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
            ]);

            $name = uniqid('audio_') . '.' .   $request->file('audio')->getClientOriginalExtension();
            $request->file('audio')->storeAs('message_audios', $name, ['disk' => 'public']);
            $message->content = 'message_audios/' . $name;
            $message->save();

            Event::dispatch(new SendMessage($message, $request->receiver, false, false));

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
            $user = auth()->user();
            $friendship = Friendship::with(['unseen_messages' => function ($query) use ($user) {
                $query->whereHas('message', function ($query) use ($user) {
                    $query->where('sender', $user->id);
                });
            }])->find($id);

            UnseenMessage::where('friendship_id', $id)->whereHas('message', function ($query) use ($user) {
                $query->where('receiver', $user->id);
            })->delete();

            $receiver_id = $user->id === $friendship->user_id ? $friendship->friend_user_id : $friendship->user_id;
            Event::dispatch(new MessageDisplayed(Collection::make([]), $receiver_id, $id));

            $messages = $friendship->messages()->orderBy('created_at', 'desc')->paginate(20);

            return response()->json([
                'friend_user' => $friendship->user()->first(),
                'unseen_messages' => $friendship->unseen_messages,
                'messages' => array_reverse($messages->items()),
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

    public function delete_unseen_message($id)
    {
        try {
            $message = Message::find($id);
            $unseen_messages = UnseenMessage::where('message_id', '!=', $id)->get();
            UnseenMessage::where('message_id', '=', $id)->delete();

            Event::dispatch(new MessageDisplayed($unseen_messages, $message->sender, $message->friendship_id));

            return response()->json([
                'messages' => 'Mensagem visualizada com sucesso',
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
