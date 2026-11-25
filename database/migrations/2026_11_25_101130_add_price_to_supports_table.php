<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->foreignId('price_id')
                ->nullable()
                ->after('type_id')
                ->constrained('supports_prices')
                ->nullOnDelete();
            $table->decimal('price', 10, 2)
                ->nullable()
                ->after('price_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('price_id');
            $table->dropColumn('price_id');
        });
    }
};
