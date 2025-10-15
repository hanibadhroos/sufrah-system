<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class adminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        
        // إنشاء كل الصلاحيات وإسنادها للأدمن
        $permissions = [
            'manage users',
            'manage tenants',
            'manage roles',
            'manage permissions',
            'manage system',
        ];

        foreach($permissions as $perm){
            $permission  = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
            $adminRole->givePermissionTo($permission);

        }

        // إنشاء مستخدم الأدمن
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => Str::uuid(),
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 1,
                'phone' => '0000000000',
            ]
        );

        // ربط الأدمن بالدور
        $admin->assignRole($adminRole);
    }
}
