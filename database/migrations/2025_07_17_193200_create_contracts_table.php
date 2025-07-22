<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('contracts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('title');
        $table->text('details');
        $table->date('start_date');
        $table->date('end_date')->nullable();
        $table->timestamps();
        $table->foreign('user_id')->references('id')->on('users');
        $table->unsignedBigInteger('subscription_plan_id')->nullable();
        $table->string('signature_path')->nullable();
        $table->date('signed_at')->nullable();
       
        
        

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
