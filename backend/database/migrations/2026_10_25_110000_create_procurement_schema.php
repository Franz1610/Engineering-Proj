<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core procurement schema, derived from INITIAL_STATE in the original script.js.
 * Readable codes (PR-1001, PO-5001, ...) are stored in a unique `code` column;
 * the numeric `id` is the real primary key used for relations.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- Access control ----------
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });

        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $t) {
            $t->foreignId('role_id')->constrained()->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $t->primary(['role_id', 'permission_id']);
        });

        Schema::create('departments', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('name');
            $t->string('contact')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('category')->nullable()->index();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->string('username')->unique()->after('id');
            $t->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
            $t->foreignId('department_id')->nullable()->after('role_id')->constrained()->nullOnDelete();
            $t->foreignId('supplier_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $t->string('status')->default('Active')->index()->after('supplier_id');
            $t->softDeletes();
        });

        // ---------- Bidding ----------
        Schema::create('stock_needs', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('title');
            $t->unsignedInteger('target_qty');
            $t->decimal('budget_cap', 14, 2);
            $t->text('tech_specs')->nullable();
            $t->string('status')->default('Published')->index();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('bids', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('stock_need_id')->constrained()->cascadeOnDelete();
            $t->foreignId('supplier_id')->constrained();
            $t->decimal('unit_price', 14, 2);
            $t->decimal('total_cost', 14, 2);
            $t->unsignedInteger('lead_time_days')->nullable();
            $t->text('notes')->nullable();
            $t->string('status')->default('Submitted')->index();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
        });

        Schema::create('proposal_meetings', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('stock_need_id')->constrained()->cascadeOnDelete();
            $t->foreignId('bid_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('supplier_id')->constrained();
            $t->date('date');
            $t->time('time');
            $t->string('venue');
            $t->string('status')->default('Pending Response')->index();
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        // ---------- Procurement flow ----------
        Schema::create('purchase_requisitions', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('department_id')->constrained();
            $t->foreignId('requested_by')->constrained('users');
            $t->string('item_title');
            $t->text('specs')->nullable();
            $t->date('target_date')->nullable();
            $t->decimal('unit_price', 14, 2);
            $t->unsignedInteger('quantity');
            $t->decimal('total_cost', 14, 2);
            $t->string('status')->default('Pending')->index();
            $t->text('remarks')->nullable();
            $t->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('purchase_requisition_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('supplier_id')->constrained();
            $t->foreignId('department_id')->constrained();
            $t->string('item_title');
            $t->text('specs')->nullable();
            $t->unsignedInteger('quantity');
            $t->decimal('total_cost', 14, 2);
            $t->date('po_date');
            $t->string('status')->default('Issued')->index();
            $t->string('type')->default('Purchase Order');
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('ordered_qty');
            $t->unsignedInteger('delivered_qty')->default(0);
            $t->date('expected_date')->nullable();
            $t->date('actual_date')->nullable();
            $t->string('delivery_status')->index();
            $t->string('compliance_status')->nullable();
            $t->text('remarks')->nullable();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('amendments', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();
            $t->text('reason');
            $t->unsignedInteger('revised_qty')->nullable();
            $t->text('revised_specs')->nullable();
            $t->string('approval_status')->default('Pending Amendment')->index();
            $t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $t->decimal('total_amount', 14, 2);
            $t->boolean('delivery_verified')->default(false);
            $t->decimal('payment_amount', 14, 2)->nullable();
            $t->string('payment_method')->nullable();
            $t->date('payment_date')->nullable();
            $t->string('status')->default('Pending')->index();
            $t->string('ref_number')->nullable();
            $t->text('remarks')->nullable();
            $t->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        // ---------- Evaluation & audit ----------
        Schema::create('supplier_ratings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $t->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedTinyInteger('rating');
            $t->unsignedTinyInteger('quality_score');
            $t->unsignedTinyInteger('delivery_score');
            $t->unsignedTinyInteger('pricing_score');
            $t->text('feedback')->nullable();
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->text('details')->nullable();
            $t->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        foreach ([
            'audit_logs', 'supplier_ratings', 'payments', 'amendments', 'deliveries',
            'purchase_orders', 'purchase_requisitions', 'proposal_meetings', 'bids', 'stock_needs',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('role_id');
            $t->dropConstrainedForeignId('department_id');
            $t->dropConstrainedForeignId('supplier_id');
            $t->dropColumn(['username', 'status', 'deleted_at']);
        });

        foreach (['suppliers', 'departments', 'permission_role', 'permissions', 'roles'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
