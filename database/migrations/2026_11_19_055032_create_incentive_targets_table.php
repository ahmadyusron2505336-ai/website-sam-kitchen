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
        Schema::create('incentive_targets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedInteger('target_orders');
            $table->unsignedInteger('reward_amount');
            $table->string('reward_label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentive_targets');
    }
};
