<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAccountWithdrawPixTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_withdraw_pix', function (Blueprint $table) {
            $table->uuid('account_withdraw_id')->primary();
            $table->string('type', 50)->default('email');
            $table->string('key', 255);
            $table->timestamps();
            
            $table->foreign('account_withdraw_id')->references('id')->on('account_withdraw')->onDelete('cascade');
            $table->index(['type']);
            $table->index(['key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_withdraw_pix');
    }
}
