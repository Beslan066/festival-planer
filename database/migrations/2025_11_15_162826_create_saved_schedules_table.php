<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('saved_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('schedule_data');
            $table->json('selected_events');
            $table->string('type')->default('individual');
            $table->string('user_session_id');
            $table->json('group_members')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_schedules');
    }
};
