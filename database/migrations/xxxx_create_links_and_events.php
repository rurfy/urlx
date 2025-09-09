<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->text('target_url');
            $t->unsignedBigInteger('clicks')->default(0);
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('click_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('link_id')->constrained()->cascadeOnDelete();
            $t->string('ip_hash', 64);
            $t->string('user_agent', 255)->nullable();
            $t->string('referrer', 255)->nullable();
            $t->timestamp('clicked_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('click_events');
        Schema::dropIfExists('links');
    }
};
