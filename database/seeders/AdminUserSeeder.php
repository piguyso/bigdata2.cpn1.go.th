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
            ['email' => 'admin@sys.com'],
            [
                'name' => 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา สพป.ชุมพร เขต 1',
                'password' => Hash::make('admin1234'), // รหัสผ่านเริ่มต้น สามารถเปลี่ยนได้ภายหลัง
                'role' => 'admin',
                'logo' => null,
            ]
        );
    }
}
