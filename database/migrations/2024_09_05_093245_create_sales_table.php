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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->integer('client_id')->unsigned();
            $table->enum('service_or_product', ['service', 'product']);
            $table->string('sport_type')->nullable();
            $table->string('service_type')->nullable();
            $table->decimal('subscription_duration', 5, 2)->nullable();
            $table->integer('visits_per_week')->nullable();
            $table->integer('training_count')->nullable();
            $table->string('trainer')->nullable();
            $table->string('trainer_category')->nullable();
            $table->string('product_type')->nullable();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->decimal('cost', 10, 2)->unsigned()->default(0);
            $table->decimal('paid_amount', 10, 2)->unsigned()->default(0);
            $table->string('pay_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
