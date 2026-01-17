<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\LeadStatus;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\CommissionRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system access',
            'permissions' => json_encode([
                'manage_users', 'manage_roles', 'view_all_data', 
                'manage_products', 'manage_settings', 'view_reports'
            ]),
        ]);

        $managerRole = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Team management and oversight',
            'permissions' => json_encode([
                'view_team_data', 'approve_quotations', 'view_all_earnings'
            ]),
        ]);

        $leaderRole = Role::create([
            'name' => 'leader',
            'display_name' => 'Team Leader',
            'description' => 'Lead a sales team',
            'permissions' => json_encode([
                'view_team_performance', 'manage_team_leads'
            ]),
        ]);

        $salesRole = Role::create([
            'name' => 'sales',
            'display_name' => 'Sales',
            'description' => 'Sales representative',
            'permissions' => json_encode([
                'manage_leads', 'create_quotations', 'view_own_earnings'
            ]),
        ]);

        // Create Users
        $admin = User::create([
            'name' => 'Admin Esdea',
            'email' => 'admin@esdea.com',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'is_active' => true,
        ]);
        $admin->roles()->attach($adminRole);

        $manager = User::create([
            'name' => 'Manager Store',
            'email' => 'manager@esdea.com',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'store' => 'Jakarta Pusat',
            'is_active' => true,
        ]);
        $manager->roles()->attach($managerRole);

        $leader = User::create([
            'name' => 'Leader Team A',
            'email' => 'leader@esdea.com',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'store' => 'Jakarta Pusat',
            'is_active' => true,
        ]);
        $leader->roles()->attach($leaderRole);

        $sales = User::create([
            'name' => 'Sales Agent',
            'email' => 'sales@esdea.com',
            'password' => Hash::make('password'),
            'phone' => '081234567893',
            'store' => 'Jakarta Pusat',
            'is_active' => true,
        ]);
        $sales->roles()->attach($salesRole);

        // Create Lead Statuses
        LeadStatus::create(['name' => 'new_lead', 'display_name' => 'New Lead', 'color' => '#3B82F6', 'order' => 1]);
        LeadStatus::create(['name' => 'contacted', 'display_name' => 'Contacted', 'color' => '#8B5CF6', 'order' => 2]);
        LeadStatus::create(['name' => 'response', 'display_name' => 'Response', 'color' => '#EC4899', 'order' => 3]);
        LeadStatus::create(['name' => 'quotation', 'display_name' => 'Quotation Sent', 'color' => '#F59E0B', 'order' => 4]);
        LeadStatus::create(['name' => 'sales', 'display_name' => 'Sales', 'color' => '#10B981', 'order' => 5]);
        LeadStatus::create(['name' => 'lost', 'display_name' => 'Lost', 'color' => '#EF4444', 'order' => 6]);

        // Create Product Categories
        $legalitas = ProductCategory::create([
            'name' => 'Legalitas & Perizinan',
            'description' => 'Layanan legalitas dan perizinan usaha',
        ]);

        $sertifikasi = ProductCategory::create([
            'name' => 'Sertifikasi',
            'description' => 'Layanan sertifikasi badan usaha dan kompetensi',
        ]);

        // Create Products
        Product::create([
            'category_id' => $legalitas->id,
            'name' => 'Sertifikasi SILO',
            'code' => 'SILO-001',
            'description' => 'Sertifikat Izin Lokasi Online',
            'base_price' => 5000000,
            'sell_price' => 7500000,
            'unit' => 'layanan',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $legalitas->id,
            'name' => 'Sertifikasi SIO',
            'code' => 'SIO-001',
            'description' => 'Sertifikat Izin Operasional',
            'base_price' => 6000000,
            'sell_price' => 8500000,
            'unit' => 'layanan',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $sertifikasi->id,
            'name' => 'NIB (Nomor Induk Berusaha)',
            'code' => 'NIB-001',
            'description' => 'Pengurusan Nomor Induk Berusaha',
            'base_price' => 3000000,
            'sell_price' => 4500000,
            'unit' => 'layanan',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $sertifikasi->id,
            'name' => 'BPOM',
            'code' => 'BPOM-001',
            'description' => 'Pengurusan izin BPOM',
            'base_price' => 8000000,
            'sell_price' => 12000000,
            'unit' => 'layanan',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $sertifikasi->id,
            'name' => 'Halal MUI',
            'code' => 'HALAL-001',
            'description' => 'Sertifikat Halal dari MUI',
            'base_price' => 7000000,
            'sell_price' => 10000000,
            'unit' => 'layanan',
            'is_active' => true,
        ]);

        // Create Commission Rules
        // Sales: 10% commission
        CommissionRule::create([
            'role_id' => $salesRole->id,
            'product_id' => null, // Apply to all products
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'min_transaction' => 0,
            'is_active' => true,
        ]);

        // Leader: Fixed Rp 50,000 per product
        CommissionRule::create([
            'role_id' => $leaderRole->id,
            'product_id' => null,
            'commission_type' => 'fixed',
            'commission_value' => 50000,
            'min_transaction' => 0,
            'is_active' => true,
        ]);

        // Manager: Fixed Rp 100,000 per product
        CommissionRule::create([
            'role_id' => $managerRole->id,
            'product_id' => null,
            'commission_type' => 'fixed',
            'commission_value' => 100000,
            'min_transaction' => 0,
            'is_active' => true,
        ]);
    }
}
