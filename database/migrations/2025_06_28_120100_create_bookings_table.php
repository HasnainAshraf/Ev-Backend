<?php

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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->foreignId('port_id')->constrained()->onDelete('cascade');
            $table->dateTime('timeslot');
            $table->enum('status', ['Pending', 'Accepted', 'Rejected'])->default('Pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Index for efficient queries
            $table->index(['port_id', 'timeslot', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
}; 