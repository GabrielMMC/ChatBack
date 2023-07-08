<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unseen_messages', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->foreignUuid('friendship_id')->references('id')->on('friendships')->onDelete('cascade');
            $table->foreignUuid('message_id')->references('id')->on('messages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
