<?php

namespace App\Http\Controllers;

use App\Events\Chat\Invites;
use App\Http\Requests\FriendshipRequest;
use App\Http\Resources\FriendshipResource;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class FriendshipController extends Controller
{
    public function store_friendship(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();

            $has_invite = Friendship::where(['user_id' => $request->friend_user_id, 'friend_user_id' => $user->id])->exists();

            if ($has_invite) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Convite em pendente com esse usuário',
                ], 500, [], JSON_PRETTY_PRINT);
            }

            $friendship = new Friendship();
            $friendship->fill([
                'user_id' => $user->id,
                'friend_user_id' => $request->friend_user_id,
                'pending' => true
            ])->save();

            Event::dispatch(new Invites($friendship, User::find($request->friend_user_id), false));

            DB::commit();
            return response()->json([
                'message' => 'Convite enviado com sucesso'
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao enviar convite',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function list_friendships()
    {
        try {
            $user = auth()->user();

            $friendships = Friendship::where(function ($query) use ($user) {
                $query->where(['user_id' => $user->id])
                    ->orWhere(['friend_user_id' => $user->id]);
            })
                ->where('pending', false)
                ->withCount('unseen_messages')
                ->orderBy('unseen_messages_count', 'desc')
                ->get();


            return response()->json([
                'friendships' => FriendshipResource::collection($friendships),
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao listar usuários adicionados',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function get_disponible_users(Request $request)
    {
        try {
            $user = auth()->user();

            // $users = Friendship::where('user_id', $user->id)
            //     ->pluck('friend_user_id')
            //     ->toArray();

            $disponible_users = User::where('id', '!=', $user->id)
                ->whereDoesntHave('friendships', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('friend_user_id', $user->id);
                })
                ->whereRaw('LOWER(nickname) LIKE ?', ['%' . strtolower($request->search) . '%'])
                ->select(['id', 'nickname'])
                ->paginate(5);


            return response()->json([
                // 'test' => $test,
                'users' => $disponible_users->items(),
                'pagination' => [
                    'current_page' => $disponible_users->currentPage(),
                    'last_page' => $disponible_users->lastPage(),
                    'total_pages' => $disponible_users->total(),
                    'per_page' => $disponible_users->perPage(),
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao listar usuários',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function get_invites(Request $request)
    {
        try {
            $user = auth()->user();

            $invites = Friendship::where(['friend_user_id' => $user->id, 'pending' => true])->with('user')->paginate(10);

            return response()->json([
                'invites' => collect($invites->items())->map(function ($item) {
                    return [
                        'id' => $item['id'],
                        'user' => [
                            'id' => $item['user']['id'],
                            'nickname' => $item['user']['nickname'],
                            'file' => $item['user']['file'],
                            'online' => $item['user']['online'],
                            'last_online_date' => $item['user']['last_online_date']
                        ]
                    ];
                }),
                'pagination' => [
                    'current_page' => $invites->currentPage(),
                    'last_page' => $invites->lastPage(),
                    'total_pages' => $invites->total(),
                    'per_page' => $invites->perPage(),
                ],
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao listar usuários',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function accept_invite($id)
    {
        try {
            $user = auth()->user();
            // return $request;
            $invite = Friendship::findOrFail($id);
            $invite->fill(['pending' => false])->save();
            $friend_id = ($invite->user_id === $user->id ? $invite->friend_user_id : $invite->user_id);

            Event::dispatch(new Invites($invite, User::find($user->id), $friend_id));

            return response()->json([
                'message' => 'Convite aceito com sucesso'
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao aceitar convite',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function decline_invite($id)
    {
        try {
            $user = auth()->user();

            Friendship::findOrFail($id)->delete();

            return response()->json([
                'message' => 'Convite rejeitado com sucesso'
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao rejeitar convite',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ]
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
