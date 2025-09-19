<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
        	'name' => 'Admin', 
        	'type' => 'admin', 
        	'email' => 'admin@gmail.com',
        	'password' => Hash::make('admin')
        ]);
        $role = Role::create([
            'name' => 'admin',
            'status' => 1,
            'created_at' => date("Y-m-d H:i:s")
        ]);
        $permissions = Permission::pluck('id','id')->all();
        $role->syncPermissions($permissions);
        $user->assignRole([$role->id]);
    }
}
