<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtpToUsersTable extends Migration
{
    public function up() : void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp')->nullable()->after('email_verified_at'); // For storing the OTP
            $table->timestamp('otp_expires_at')->nullable()->after('otp'); // For storing OTP expiration time
        });
    }

    public function down() : void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }
}
