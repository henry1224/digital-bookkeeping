<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('type', 30);
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('is_postable')->default(true);
            $table->string('report_group', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('outlet_configs', function (Blueprint $table) {
            $table->foreign('default_cash_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('default_bank_account_id')->references('id')->on('accounts')->nullOnDelete();
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });

        Schema::create('posting_rules', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 50);
            $table->foreignId('debit_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->foreignId('credit_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->string('condition_key', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['source_type', 'condition_key']);
        });

        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_no')->unique();
            $table->date('journal_date');
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status', 30)->default('draft');
            $table->decimal('total_debit_amount', 18, 2)->default(0);
            $table->decimal('total_credit_amount', 18, 2)->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit_amount', 18, 2)->default(0);
            $table->decimal('credit_amount', 18, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('auditable_type', 150);
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('posting_rules');

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });

        Schema::table('outlet_configs', function (Blueprint $table) {
            $table->dropForeign(['default_cash_account_id']);
            $table->dropForeign(['default_bank_account_id']);
        });

        Schema::dropIfExists('accounts');
    }
};
