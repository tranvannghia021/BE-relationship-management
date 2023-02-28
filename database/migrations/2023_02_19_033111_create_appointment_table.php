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
        Schema::create('appointment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('relationship_id');
            $table->foreign('relationship_id')->references('id')->on('relationships');
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->date('time')->default(date("Y-m-d H:i:s"));
            $table->jsonb('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment');
    }
};
