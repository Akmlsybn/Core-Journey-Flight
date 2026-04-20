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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('booking_code');
            $table->string('payment_method')->nullable()->after('status');
            $table->timestamp('payment_expires_at')->nullable()->after('payment_method');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');

            $table->index('payment_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['payment_expires_at']);
            $table->dropColumn([
                'customer_email',
                'payment_method',
                'payment_expires_at',
                'cancelled_at',
            ]);
        });
    }
};
