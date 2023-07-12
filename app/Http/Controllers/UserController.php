<?php

namespace App\Http\Controllers;

use App\Events\Chat\SendMessage;
use App\Events\Chat\Status;
use App\Http\Requests\UserRequest;
use App\Models\Friendship;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user = new User();

            $has_email = User::where('email', '=', $data['email'])->exists();
            $has_nickname = User::where('nickname', '=', $data['nickname'])->exists();

            // Checking if the request email has already been used
            if ($has_email) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Email em uso'
                ], 500, [], JSON_PRETTY_PRINT);
            }

            // Checking if the request nickname has already been used
            if ($has_nickname) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Apelido em uso'
                ], 500, [], JSON_PRETTY_PRINT);
            }

            $user->fill([...$data, 'password' => bcrypt($data['password'])])->save();

            DB::commit();
            return response()->json([
                'message' => 'Conta criada com sucesso'
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar conta',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function login(UserRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::firstWhere("email", $data["email"]);

            if (!password_verify($data["password"], $user->password))
                return response()->json([
                    'message' => 'E-mail ou senha incorretos'
                ], 401);

            // $token = $user->createToken('token')->plainTextToken;
            $token = $user->createToken('token')->accessToken;

            return response()->json([
                "user" => $user,
                "access_token" => $token,
                "token_type" => "Bearer"
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao realizar login',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function get_user()
    {
        try {
            $user = auth()->user();

            return response()->json([
                "user" => $user,
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao carregar dados de usuário',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function get_valid_nickname($nickname)
    {
        try {
            $user = auth()->user();

            if (User::where('nickname', $nickname)->where('id', '!=', $user->id)->exists()) {
                throw new \Exception('Nickname já existente');
            }

            return response()->json([
                "user" => $user,
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'except' => $ex,
                'message' => 'Erro ao carregar dados de usuário',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function update_user(UserRequest $request)
    {
        try {
            $data = $request->validated();

            $user = auth()->user();

            if (User::where('nickname', $data['nickname'])->where('id', '!=', $user->id)->exists()) {
                throw new \Exception('Nickname já existente');
            }

            $user = User::findOrFail($user->id);
            $user->fill($data);

            if ($request->file('image')) {
                // Verifique se a imagem é acessível
                if (!$request->file('image')->isReadable()) {
                    throw new \Exception('A imagem ' .  $request->file('image')->getClientOriginalExtension() . ' não é acessível');
                }
                // Defina o tamanho máximo permitido para a imagem em bytes
                $size = $request->file('image')->getSize();
                // Verifique se o tamanho da imagem é menor ou igual ao tamanho máximo permitido
                if ($size > 1024 * (1024 * 5)) {
                    throw new \Exception('O tamanho da imagem é muito grande');
                }

                // Verifica se o usuário já possui uma imagem antiga
                if ($user->file) {
                    // Exclui a imagem antiga do storage
                    Storage::disk('public')->delete($user->file);
                }

                $img =  $request->file('image');
                $name = uniqid('img_') . '.' .   $img->getClientOriginalExtension();
                $img->storeAs('images', $name, ['disk' => 'public']);
                $user->file = 'images/' . $name;
            }

            $user->save();

            return response()->json([
                'message' => 'Dados atualizados com sucesso',
                "user" => $user,
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao atualizar usuário',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function update_online_status($status)
    {
        try {
            $user = auth()->user();
            $user = User::findOrFail($user->id);

            $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);

            if ($status === true) {
                $user->fill([
                    'online' => true
                ])->save();
            } else {
                $user->fill([
                    'online' => false,
                    'last_online_date' => Carbon::now(),
                ])->save();
            }

            Event::dispatch(new Status($user->id, $status, false, $user->last_online_date, 'online_response'));

            return response()->json([
                "user" => $user,
                'status' => $status
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao alterar status de usuário',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function update_is_typing_status($status, $receiver_id)
    {
        try {
            // $user = auth()->user();
            // $user = User::findOrFail($user->id);

            $status = filter_var($status, FILTER_VALIDATE_BOOLEAN);

            Event::dispatch(new SendMessage(null, $receiver_id, $status, true));

            return response()->json([
                'status' => $status
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao alterar status de digitação',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }
}
