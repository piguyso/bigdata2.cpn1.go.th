<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('obec_asset_imports') && ! Schema::hasColumn('obec_asset_imports', 'academic_year')) {
            Schema::table('obec_asset_imports', function (Blueprint $table) {
                $table->string('academic_year', 4)->default('')->after('area_name');
                $table->string('term', 10)->default('')->after('academic_year');
                $table->index(['area_code', 'academic_year', 'term'], 'obec_asset_imports_area_year_term_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('obec_asset_imports') && Schema::hasColumn('obec_asset_imports', 'academic_year')) {
            Schema::table('obec_asset_imports', function (Blueprint $table) {
                $table->dropIndex('obec_asset_imports_area_year_term_idx');
                $table->dropColumn(['academic_year', 'term']);
            });
        }
    }
};
