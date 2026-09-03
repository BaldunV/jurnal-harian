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
        Schema::dropIfExists('otp_devices');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('users', 'otp_channel')) {
                $table->dropColumn('otp_channel');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Penghapusan sistem OTP bersifat permanen.
    }
};
