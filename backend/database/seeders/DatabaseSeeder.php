<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Bid;
use App\Models\Delivery;
use App\Models\Department;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\ProposalMeeting;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Role;
use App\Models\StockNeed;
use App\Models\Supplier;
use App\Models\SupplierRating;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the database with the same sample data as INITIAL_STATE in the old script.js.
 * Default password for all sample accounts: password123
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- Roles & permission matrix ----------
        $matrix = [
            'Admin' => ['create_pr', 'approve_pr', 'issue_po', 'record_delivery', 'manage_delivery_issues', 'approve_amendment', 'process_payment', 'confirm_payment', 'manage_users', 'rate_supplier', 'manage_permissions', 'view_all_po', 'manage_bidding'],
            'Procurement Head' => ['create_pr', 'approve_pr', 'issue_po', 'record_delivery', 'manage_delivery_issues', 'approve_amendment', 'process_payment', 'confirm_payment', 'rate_supplier', 'view_all_po', 'manage_bidding'],
            'Procurement Officer' => ['create_pr', 'issue_po', 'record_delivery', 'rate_supplier', 'view_all_po', 'manage_bidding'],
            'Employee' => ['create_pr'],
            'Supplier' => ['view_assigned_po', 'view_supplier_portal', 'submit_bids'],
        ];

        $roles = [];
        foreach ($matrix as $roleName => $keys) {
            $role = Role::create(['name' => $roleName]);
            $ids = collect($keys)->map(fn ($k) => Permission::firstOrCreate(['key' => $k])->id);
            $role->permissions()->sync($ids);
            $roles[$roleName] = $role;
        }

        // ---------- Departments ----------
        $dept = collect(['Executive', 'Procurement', 'IT Systems', 'Human Resources', 'External Vendor'])
            ->mapWithKeys(fn ($n) => [$n => Department::create(['name' => $n])]);

        // ---------- Suppliers ----------
        $globalTech = Supplier::create(['code' => 'sup-101', 'name' => 'Global Tech Supplies Inc.', 'contact' => 'Alex Rivera', 'email' => 'sales@globaltech.com', 'phone' => '+1 (555) 019-2834', 'category' => 'IT Hardware']);
        Supplier::create(['code' => 'sup-102', 'name' => 'Apex Office Solutions', 'contact' => 'Clara Bennett', 'email' => 'orders@apexoffice.com', 'phone' => '+1 (555) 018-9921', 'category' => 'Furniture & Supplies']);
        Supplier::create(['code' => 'sup-103', 'name' => 'Industrial Maintenance Co.', 'contact' => 'Robert Vance', 'email' => 'service@indmaint.com', 'phone' => '+1 (555) 012-3344', 'category' => 'Services & Maintenance']);

        // ---------- Users (passwords are hashed automatically) ----------
        $mk = fn ($u, $n, $r, $d, $e, $s = null) => User::create([
            'username' => $u, 'name' => $n, 'email' => $e, 'password' => 'password123',
            'role_id' => $roles[$r]->id, 'department_id' => $dept[$d]->id, 'supplier_id' => $s, 'status' => 'Active',
        ]);
        $admin = $mk('admin', 'System Administrator', 'Admin', 'Executive', 'admin@enterprise.com');
        $head = $mk('head_proc', 'Eleanor Vance', 'Procurement Head', 'Procurement', 'eleanor@enterprise.com');
        $officer = $mk('proc_officer', 'Marcus Brody', 'Procurement Officer', 'Procurement', 'marcus@enterprise.com');
        $john = $mk('emp_john', 'John Doe', 'Employee', 'IT Systems', 'john@enterprise.com');
        $mk('vendor_global', 'Alex Rivera', 'Supplier', 'External Vendor', 'sales@globaltech.com', $globalTech->id);

        // ---------- Bidding ----------
        $need = StockNeed::create(['code' => 'SN-2001', 'title' => 'High-Performance Cloud Servers', 'target_qty' => 10, 'budget_cap' => 50000, 'tech_specs' => '2U Rack Server, Dual Xeon Silver, 128GB RAM, 4x2TB NVMe SSDs in RAID 10', 'status' => 'Published', 'created_by' => $admin->id]);
        $bid = Bid::create(['code' => 'BID-3001', 'stock_need_id' => $need->id, 'supplier_id' => $globalTech->id, 'unit_price' => 4800, 'total_cost' => 48000, 'lead_time_days' => 14, 'notes' => 'Includes 3 years 24/7 technical support and next-business-day hardware replacement warranty.', 'status' => 'Submitted', 'submitted_at' => '2026-10-24 11:30']);
        ProposalMeeting::create(['code' => 'MTG-4001', 'stock_need_id' => $need->id, 'bid_id' => $bid->id, 'supplier_id' => $globalTech->id, 'date' => '2026-10-28', 'time' => '14:00', 'venue' => 'HQ Executive Conference Room B & Online Stream', 'status' => 'Pending Response', 'notes' => 'Technical specification presentation and price negotiation round.']);

        // ---------- Requisitions ----------
        $pr1 = PurchaseRequisition::create(['code' => 'PR-1001', 'department_id' => $dept['IT Systems']->id, 'requested_by' => $john->id, 'item_title' => 'Dell XPS 15 Workstations', 'specs' => 'Intel Core i9, 32GB RAM, 1TB NVMe SSD, RTX 4060, 15.6" UHD Display', 'target_date' => '2026-11-15', 'unit_price' => 2200, 'quantity' => 5, 'total_cost' => 11000, 'status' => 'In Process', 'remarks' => 'Urgent replacement for software development team.']);
        PurchaseRequisition::create(['code' => 'PR-1002', 'department_id' => $dept['Human Resources']->id, 'requested_by' => $john->id, 'item_title' => 'Ergonomic Mesh Office Chairs', 'specs' => 'High-back mesh, adjustable lumbar support, 3D armrests, heavy duty base', 'target_date' => '2026-11-20', 'unit_price' => 250, 'quantity' => 12, 'total_cost' => 3000, 'status' => 'Pending', 'remarks' => 'Annual office furniture upgrade.']);

        // ---------- PO → Delivery → Payment ----------
        $po = PurchaseOrder::create(['code' => 'PO-5001', 'purchase_requisition_id' => $pr1->id, 'supplier_id' => $globalTech->id, 'department_id' => $dept['IT Systems']->id, 'item_title' => $pr1->item_title, 'specs' => $pr1->specs, 'quantity' => 5, 'total_cost' => 11000, 'po_date' => '2026-10-21', 'status' => 'Issued', 'type' => 'Purchase Order', 'created_by' => $officer->id]);
        Delivery::create(['code' => 'DEL-8001', 'purchase_order_id' => $po->id, 'ordered_qty' => 5, 'delivered_qty' => 5, 'expected_date' => '2026-10-24', 'actual_date' => '2026-10-24', 'delivery_status' => 'Delivered', 'compliance_status' => 'Completed - Verified', 'remarks' => 'All 5 units delivered in perfect box sealing and benchmark tested.', 'recorded_by' => $officer->id]);
        Payment::create(['code' => 'PAY-9001', 'purchase_order_id' => $po->id, 'total_amount' => 11000, 'delivery_verified' => true, 'payment_amount' => 11000, 'payment_method' => 'Bank Transfer', 'payment_date' => '2026-10-24', 'status' => 'Paid', 'ref_number' => 'TRX-998823104', 'remarks' => 'Processed via wire transfer. Invoice #GT-8842 attached.', 'processed_by' => $admin->id]);

        // ---------- Rating & audit ----------
        SupplierRating::create(['supplier_id' => $globalTech->id, 'evaluator_id' => $head->id, 'rating' => 5, 'quality_score' => 5, 'delivery_score' => 5, 'pricing_score' => 4, 'feedback' => 'Exceptional delivery turnaround time and immaculate hardware condition.']);
        AuditLog::create(['user_id' => $admin->id, 'action' => 'System Initialization', 'details' => 'Database seeded with initial procurement data.']);
    }
}
