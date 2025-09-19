<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $tempArr = array(
        //     array('role_name' => 'admin','status' => 1),
        //  );
         
        //  foreach ($tempArr as $key => $value) {
        //     $role = Role::create([
        //         'name' => $value['role_name'],
        //         'status' => $value['status'],
        //         'created_at' => date("Y-m-d H:i:s")
        //     ]);
        //     // $permissions = Permission::pluck('id','id')->all();
        //     // $role->syncPermissions($permissions);
        //     // $user->assignRole([$role->id]);
        // }
    }
}
