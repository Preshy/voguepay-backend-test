<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id');
            $table->string('transaction_id');
            $table->string('reference');
            $table->decimal('amount', 8, 2);
            $table->string('narration')->nullable();

            $table->string('beneficiary_name');
            $table->string('beneficiary_phone')->nullable();
            $table->string('bank_code');
            $table->bigInteger('account_number');

            $table->string('gateway')->default('voguepay');
            $table->string('status')->default('pending');

            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
