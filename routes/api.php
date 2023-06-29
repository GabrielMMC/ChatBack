<?php

use App\Http\Controllers\FriendshipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
    "prefix" => "auth"
], function () {
    Route::post("login", [UserController::class, "login"]);
    Route::post("register", [UserController::class, "register"]);
});

Route::group([
    "prefix" => "messages"
], function () {
    Route::group([
        "middleware" => "auth:api"
    ], function () {
        Route::get("/{id}", [MessageController::class, "get_messages"]);
        Route::post("/create", [MessageController::class, "store_message"]);
        Route::put("/update", [MessageController::class, "update_message"]);
        Route::delete("/delete/{id}", [MessageController::class, "delete_message"]);
    });
});

Route::group([
    "prefix" => "friendships"
], function () {
    Route::group([
        "middleware" => "auth:api"
    ], function () {
        Route::get("/", [FriendshipController::class, "list_friendships"]);
        Route::post("/create", [FriendshipController::class, "store_friendship"]);
        Route::put("/update/{id}", [FriendshipController::class, "accept_invite"]);
        Route::get("/get_invites", [FriendshipController::class, "get_invites"]);
        Route::get("/get_disponible_users", [FriendshipController::class, "get_disponible_users"]);
        Route::delete("/delete/{id}", [FriendshipController::class, "decline_invite"]);
    });
});

Route::group([
    "prefix" => "profile"
], function () {
    Route::group([
        "middleware" => "auth:api"
    ], function () {
        Route::get("/", [UserController::class, "get_user"]);
        Route::post("/update", [UserController::class, "update_user"]);
        Route::get("/nickname/{nickname}", [UserController::class, "get_valid_nickname"]);
        Route::put("/online/{status}", [UserController::class, "update_online_status"]);
    });
});
