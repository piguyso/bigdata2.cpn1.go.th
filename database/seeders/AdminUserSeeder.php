<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@abcp.ac.th'],
            [
                'name' => 'ศูนย์การพัฒนาครูและบุคลากรทางการศึกษา สสวท.',
                'password' => Hash::make('admin1234'), // รหัสผ่านเริ่มต้น สามารถเปลี่ยนได้ภายหลัง
                'role' => 'admin',
                'logo' => null,
            ]
        );
    }
}
