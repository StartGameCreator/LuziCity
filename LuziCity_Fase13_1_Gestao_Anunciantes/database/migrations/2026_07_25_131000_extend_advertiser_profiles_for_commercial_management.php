<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('advertiser_profiles')) {
            Schema::table('advertiser_profiles', function (Blueprint $table): void {
                if (! Schema::hasColumn('advertiser_profiles', 'legal_name')) $table->string('legal_name')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'trade_name')) $table->string('trade_name')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'state_registration')) $table->string('state_registration')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'municipal_registration')) $table->string('municipal_registration')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'segment')) $table->string('segment')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'company_size')) $table->string('company_size', 30)->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'commercial_status')) $table->string('commercial_status', 40)->default('prospect');
                if (! Schema::hasColumn('advertiser_profiles', 'responsible_user_id')) $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
                if (! Schema::hasColumn('advertiser_profiles', 'email')) $table->string('email')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'whatsapp')) $table->string('whatsapp')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'social_links')) $table->text('social_links')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'notes')) $table->text('notes')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'contracted_revenue')) $table->decimal('contracted_revenue', 14, 2)->default(0);
                if (! Schema::hasColumn('advertiser_profiles', 'expected_revenue')) $table->decimal('expected_revenue', 14, 2)->default(0);
                if (! Schema::hasColumn('advertiser_profiles', 'contract_starts_at')) $table->date('contract_starts_at')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'contract_ends_at')) $table->date('contract_ends_at')->nullable();
                if (! Schema::hasColumn('advertiser_profiles', 'is_active')) $table->boolean('is_active')->default(true);
            });
        }

        if (! Schema::hasTable('advertiser_contacts')) {
            Schema::create('advertiser_contacts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('advertiser_profile_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('position')->nullable();
                $table->string('phone')->nullable();
                $table->string('whatsapp')->nullable();
                $table->string('email')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertiser_addresses')) {
            Schema::create('advertiser_addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('advertiser_profile_id')->constrained()->cascadeOnDelete();
                $table->string('type', 30)->default('commercial');
                $table->string('postal_code', 12)->nullable();
                $table->string('street')->nullable();
                $table->string('number', 30)->nullable();
                $table->string('complement')->nullable();
                $table->string('district')->nullable();
                $table->string('city')->nullable();
                $table->string('state', 2)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertiser_documents')) {
            Schema::create('advertiser_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('advertiser_profile_id')->constrained()->cascadeOnDelete();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('type', 40)->default('other');
                $table->string('name');
                $table->string('path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertiser_histories')) {
            Schema::create('advertiser_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('advertiser_profile_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 40)->default('note');
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('occurred_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertiser_histories');
        Schema::dropIfExists('advertiser_documents');
        Schema::dropIfExists('advertiser_addresses');
        Schema::dropIfExists('advertiser_contacts');
        // Campos adicionados ao cadastro-base são preservados para rollback seguro de dados.
    }
};
