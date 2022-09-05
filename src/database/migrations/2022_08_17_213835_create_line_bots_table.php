<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_bots', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('basic_id', 16)->nullable();
            $table->string('channel_id', 32)->nullable();
            $table->string('channel_access_token', 256);
            $table->string('channel_secret', 64);
            $table->string('liff_id', 32)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_bots');
    }
};
