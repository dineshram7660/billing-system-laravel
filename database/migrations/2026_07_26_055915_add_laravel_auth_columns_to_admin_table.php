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
        Schema::table('admin', function (Blueprint $table) {
            // The legacy app hashes passwords with unsalted MD5. We keep the
            // password column as-is (bcrypt hashes fit fine in TEXT) and
            // upgrade each admin's hash to bcrypt the next time they log in
            // successfully — see App\Http\Requests\Auth\LoginRequest.
            $table->rememberToken()->after('password');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn(['remember_token', 'email_verified_at', 'created_at', 'updated_at']);
        });
    }
};
