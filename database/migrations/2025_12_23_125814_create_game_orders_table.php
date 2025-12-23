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
        Schema::create('game_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('game_code', 32);
            $table->string('reference_id');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAILED'])->default('PENDING');
            $table->timestamps();
        
            $table->index(['reference_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_orders');
    }
};
