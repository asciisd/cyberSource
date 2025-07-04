<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCybersourceTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cybersource_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique()->index();
            $table->string('transaction_id')->unique()->index();
            $table->string('reference')->nullable()->index();
            $table->string('status');
            $table->string('payment_method')->default('card');
            $table->string('card_type')->nullable();
            $table->string('name')->nullable();
            $table->string('last_four')->nullable();
            $table->string('amount');
            $table->string('currency', 3);
            $table->string('response_code')->nullable();
            $table->string('approval_code')->nullable();
            $table->string('reconciliation_id')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cybersource_transactions');
    }
}
