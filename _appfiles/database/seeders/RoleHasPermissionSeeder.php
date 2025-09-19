<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RoleHasPermission;

class RoleHasPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tempArr = array(
            array('role_id' => 1,'permission_id' => 1),
            array('role_id' => 1,'permission_id' => 2),
            array('role_id' => 1,'permission_id' => 3),
            array('role_id' => 1,'permission_id' => 4),
            array('role_id' => 1,'permission_id' => 5),
            array('role_id' => 1,'permission_id' => 6),
            array('role_id' => 1,'permission_id' => 7),
            array('role_id' => 1,'permission_id' => 8),
            array('role_id' => 1,'permission_id' => 9),
            array('role_id' => 1,'permission_id' => 10),            
            array('role_id' => 1,'permission_id' => 11),
            array('role_id' => 1,'permission_id' => 12),
            array('role_id' => 1,'permission_id' => 13),
            array('role_id' => 1,'permission_id' => 14),
            array('role_id' => 1,'permission_id' => 15),
            array('role_id' => 1,'permission_id' => 16),
            array('role_id' => 1,'permission_id' => 17),
            array('role_id' => 1,'permission_id' => 18),
            array('role_id' => 1,'permission_id' => 19),
            array('role_id' => 1,'permission_id' => 20),
            array('role_id' => 1,'permission_id' => 21),
            array('role_id' => 1,'permission_id' => 22),
            array('role_id' => 1,'permission_id' => 23),
            array('role_id' => 1,'permission_id' => 24),
            array('role_id' => 1,'permission_id' => 25),
            array('role_id' => 1,'permission_id' => 26),
            array('role_id' => 1,'permission_id' => 27),
            array('role_id' => 1,'permission_id' => 28),
            array('role_id' => 1,'permission_id' => 29),
            array('role_id' => 1,'permission_id' => 30),
            array('role_id' => 1,'permission_id' => 31),
            array('role_id' => 1,'permission_id' => 32),
            array('role_id' => 1,'permission_id' => 33),
            array('role_id' => 1,'permission_id' => 34),
            array('role_id' => 1,'permission_id' => 35),
            array('role_id' => 1,'permission_id' => 36),
            array('role_id' => 1,'permission_id' => 37),
            array('role_id' => 1,'permission_id' => 38),
            array('role_id' => 1,'permission_id' => 39),
            array('role_id' => 1,'permission_id' => 40),
            array('role_id' => 1,'permission_id' => 41),
            array('role_id' => 1,'permission_id' => 42),
            array('role_id' => 1,'permission_id' => 43),
            array('role_id' => 1,'permission_id' => 44),
            array('role_id' => 1,'permission_id' => 45),
            array('role_id' => 1,'permission_id' => 46),
            array('role_id' => 1,'permission_id' => 47),
            array('role_id' => 1,'permission_id' => 48),
            array('role_id' => 1,'permission_id' => 49),
            array('role_id' => 1,'permission_id' => 50),
            array('role_id' => 1,'permission_id' => 51),
            array('role_id' => 1,'permission_id' => 52),
            array('role_id' => 1,'permission_id' => 53),
            array('role_id' => 1,'permission_id' => 54),
            array('role_id' => 1,'permission_id' => 55),
            array('role_id' => 1,'permission_id' => 56),
            array('role_id' => 1,'permission_id' => 57),
            array('role_id' => 1,'permission_id' => 58),
            array('role_id' => 1,'permission_id' => 59),
            array('role_id' => 1,'permission_id' => 60),
            array('role_id' => 1,'permission_id' => 61),
            array('role_id' => 1,'permission_id' => 62),
            array('role_id' => 1,'permission_id' => 63),
            array('role_id' => 1,'permission_id' => 64),
            array('role_id' => 1,'permission_id' => 65),
            array('role_id' => 1,'permission_id' => 66),
            array('role_id' => 1,'permission_id' => 67),
            array('role_id' => 1,'permission_id' => 68),
            array('role_id' => 1,'permission_id' => 69),
            array('role_id' => 1,'permission_id' => 70),
            array('role_id' => 1,'permission_id' => 71),
            array('role_id' => 1,'permission_id' => 72),            
            array('role_id' => 1,'permission_id' => 73),
            array('role_id' => 1,'permission_id' => 74),
            array('role_id' => 1,'permission_id' => 75),
            array('role_id' => 1,'permission_id' => 76),
            array('role_id' => 1,'permission_id' => 77),
            array('role_id' => 1,'permission_id' => 78),
            array('role_id' => 1,'permission_id' => 79),
            array('role_id' => 1,'permission_id' => 80),
            array('role_id' => 1,'permission_id' => 81),
            array('role_id' => 1,'permission_id' => 82),
            array('role_id' => 1,'permission_id' => 83),
            array('role_id' => 1,'permission_id' => 84),
            array('role_id' => 1,'permission_id' => 85),
            array('role_id' => 1,'permission_id' => 86),
            array('role_id' => 1,'permission_id' => 87),
            array('role_id' => 1,'permission_id' => 88),
            array('role_id' => 1,'permission_id' => 89),
            array('role_id' => 1,'permission_id' => 90),
            array('role_id' => 1,'permission_id' => 91),
            array('role_id' => 1,'permission_id' => 92),
        );
         
        foreach ($tempArr as $key => $value) {
            RoleHasPermission::insert([
                 'role_id' => $value['role_id'],
                 'permission_id' => $value['permission_id'],
                 'created_at' => date("Y-m-d H:i:s")
            ]);
        }
    }
}
