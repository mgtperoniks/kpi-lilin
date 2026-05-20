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
        Schema::table('hr_reports', function (Blueprint $table) {
            // Use 'status' as the only guaranteed anchor
            if (!Schema::hasColumn('hr_reports', 'approval_status')) {
                $table->enum('approval_status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->after('status');
            }
            if (!Schema::hasColumn('hr_reports', 'submitted_by')) {
                $table->unsignedBigInteger('submitted_by')->nullable();
            }
            if (!Schema::hasColumn('hr_reports', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }
            if (!Schema::hasColumn('hr_reports', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }
            if (!Schema::hasColumn('hr_reports', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('hr_reports', 'approval_note')) {
                $table->text('approval_note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_reports', function (Blueprint $table) {
            $cols = ['approval_status', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'approval_note'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('hr_reports', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
