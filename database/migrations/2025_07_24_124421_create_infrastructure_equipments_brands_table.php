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
        if (!Schema::hasTable('infrastructure_equipment_types')) {

            Schema::create('infrastructure_equipments_brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('status_id')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infrastructure_equipments_brands');
    }
};
