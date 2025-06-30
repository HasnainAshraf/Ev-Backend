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
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->string('port_number');
            $table->string('type')->default('Type 2'); // Type 1, Type 2, CCS, etc.
            $table->integer('power_kw')->default(22); // Power in kilowatts
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['station_id', 'port_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ports');
    }
}; 