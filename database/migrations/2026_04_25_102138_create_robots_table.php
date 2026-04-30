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
        Schema::create('robots', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('mac_address')->unique()->nullable();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(\Domain\Robot\Enums\RobotState::OFFLINE->value);
            $table->decimal('battery_pct', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robots');
    }
};
