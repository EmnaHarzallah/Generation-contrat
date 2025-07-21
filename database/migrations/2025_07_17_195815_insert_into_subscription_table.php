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
        DB::table('subscription_plans')->insert([
            'name' => 'Abonnement Basique',
            'description' => 'Description de l\'abonnement basique',
            'price' => 100,
            'duration' => 30,
        ]);
        DB::table('subscription_plans')->insert([
            'name' => 'Abonnement Basique Premium',
            'description' => 'Description de l\'abonnement basique premium',
            'price' => 200,
            'duration' => 60,
        ]);
        DB::table('subscription_plans')->insert([
            'name' => 'Abonnement Premium   ',
            'description' => 'Description de l\'abonnement premium',
            'price' => 300,
            'duration' => 90,
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('subscription_plans')->delete();  
    }
};
