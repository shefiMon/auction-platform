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
        Schema::create('auctions', function (Blueprint $table) {
             $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->decimal('starting_price', 10, 2);
            $table->decimal('current_price', 10, 2);
            $table->decimal('reserve_price', 10, 2)->nullable();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('winner_id')->nullable()->constrained('users');
            $table->boolean('auto_extend')->default(true);
            $table->integer('extension_time')->default(300); // 5 minutes in seconds
            $table->string('stream_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
