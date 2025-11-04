<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->foreignId('restaurant_table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->unsignedTinyInteger('number_of_people');
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->string('visit_purpose')->nullable();
            $table->string('occasion')->nullable();
            $table->string('source')->default('online');
            $table->text('message')->nullable();
            $table->text('reservation_notes')->nullable();
            $table->json('allergies')->nullable();
            $table->json('diets')->nullable();
            $table->enum('status', ['pending', 'awaiting_details', 'confirmed', 'cancelled'])->default('awaiting_details');
            $table->boolean('created_via_frontend')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('step_one_completed_at')->nullable();
            $table->timestamp('details_completed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->string('manage_token', 64)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};