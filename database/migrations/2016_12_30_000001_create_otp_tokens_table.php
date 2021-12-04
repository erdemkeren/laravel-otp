<?php
/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOtpTokensTable.
 */
class CreateOtpTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('otp_tokens', function (Blueprint $table) {
            $table->unsignedInteger('authenticable_id');
            $table->string('format');
            $table->string('cipher_text', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->unsignedSmallInteger('expiry_time')->nullable();

            $table->unique(['authenticable_id', 'cipher_text']);
            $table->index(['cipher_text']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('otp_tokens');
    }
}
