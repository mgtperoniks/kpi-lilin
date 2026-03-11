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
        Schema::table('process_targets', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('process_name');
            $table->string('size_name')->nullable()->after('item_name');
            $table->string('unit', 20)->default('PCS')->after('size_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_targets', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'size_name', 'unit']);
        });
    }
};
