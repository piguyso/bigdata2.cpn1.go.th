<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_school') && ! Schema::hasColumn('system_school', 'logo_path')) {
            Schema::table('system_school', function (Blueprint $table) {
                $table->string('logo_path', 500)->nullable()->after('website');
            });
        }

        Schema::create('obec_asset_imports', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 20)->index();
            $table->string('area_name', 255)->default('');
            $table->string('source_url', 500);
            $table->string('mode', 20)->default('replace');
            $table->unsignedInteger('school_rows_count')->default(0);
            $table->unsignedInteger('school_logos_count')->default(0);
            $table->unsignedInteger('building_records_count')->default(0);
            $table->unsignedInteger('matched_schools_count')->default(0);
            $table->unsignedInteger('unmatched_schools_count')->default(0);
            $table->json('warnings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['area_code', 'created_at'], 'obec_asset_imports_area_created_idx');
        });

        Schema::create('obec_asset_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('obec_asset_imports')->cascadeOnDelete();
            $table->unsignedInteger('school_id')->nullable()->index();
            $table->string('school_smis', 20)->index();
            $table->string('school_name', 255)->default('');
            $table->string('subdistrict', 120)->default('');
            $table->string('district', 120)->default('');
            $table->string('province', 120)->default('');
            $table->string('detail_url', 500)->default('');
            $table->string('logo_path', 500)->nullable();
            $table->string('logo_mime', 80)->nullable();
            $table->string('logo_hash', 64)->nullable();
            $table->unsignedInteger('logo_bytes')->default(0);
            $table->json('raw_row')->nullable();
            $table->timestamps();

            $table->index(['import_id', 'school_smis'], 'obec_asset_schools_import_smis_idx');
        });

        Schema::create('obec_asset_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('obec_asset_imports')->cascadeOnDelete();
            $table->unsignedInteger('school_id')->nullable()->index();
            $table->string('school_smis', 20)->index();
            $table->string('school_name', 255)->default('');
            $table->string('building_type', 255)->default('');
            $table->string('building_model', 500)->default('');
            $table->string('main_image_url', 500)->nullable();
            $table->unsignedSmallInteger('rooms_design')->nullable();
            $table->unsignedSmallInteger('rooms_actual')->nullable();
            $table->unsignedSmallInteger('rooms_special')->nullable();
            $table->unsignedSmallInteger('extension_classroom')->nullable();
            $table->unsignedSmallInteger('extension_special')->nullable();
            $table->unsignedSmallInteger('construction_year')->nullable();
            $table->unsignedSmallInteger('age_years')->nullable();
            $table->decimal('budget', 14, 2)->nullable();
            $table->string('budget_source', 500)->nullable();
            $table->string('condition', 120)->nullable();
            $table->string('usage_status', 120)->nullable();
            $table->json('extra_images')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['import_id', 'school_smis'], 'obec_asset_buildings_import_smis_idx');
            $table->index(['building_type'], 'obec_asset_buildings_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obec_asset_buildings');
        Schema::dropIfExists('obec_asset_schools');
        Schema::dropIfExists('obec_asset_imports');

        if (Schema::hasTable('system_school') && Schema::hasColumn('system_school', 'logo_path')) {
            Schema::table('system_school', function (Blueprint $table) {
                $table->dropColumn('logo_path');
            });
        }
    }
};
