<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(config('tracking.storage.table', 'tracking_events'), function (Blueprint $table): void {
            $table->id();
            $table->string('event');
            $table->string('driver')->nullable();
            $table->json('drivers')->nullable();
            $table->json('data')->nullable();
            $table->json('meta')->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('tracking.storage.table', 'tracking_events'));
    }
};
