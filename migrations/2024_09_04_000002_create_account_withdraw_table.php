<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAccountWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('account_withdraw', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('method', 50)->default('PIX');
            $table->decimal('amount', 15, 2);
            $table->boolean('scheduled')->default(false);
            $table->timestamp('scheduled_for')->nullable();
            $table->boolean('done')->default(false);
            $table->boolean('error')->default(false);
            $table->text('error_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('account_id')->references('id')->on('account')->onDelete('cascade');
            $table->index(['account_id', 'done']);
            $table->index(['scheduled', 'scheduled_for', 'done']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_withdraw');
    }
}
