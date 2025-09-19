<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\BlogCategory;
use App\Models\CategoryTranslation;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categoryArr = [
            array(
                'id'=>1,
                'parent_id'=>'0',
                'name' => 'Demo Category 1', 
                'slug' => \Helpers::createSlug('Demo Category 1','category',0,false),
                'color'=> '#000000',
                'order'=> '1',
                'status'=> '1',
                'is_featured'=> '1'
            ),
            array(
                'id'=>2,
                'parent_id'=>'0',
                'name' => 'Demo Category 2', 
                'slug' => \Helpers::createSlug('Demo Category 2','category',0,false),
                'color'=> '#000000',
                'order'=> '1',
                'status'=> '1',
                'is_featured'=> '1'
            ),
            array(
                'id'=>3,
                'parent_id'=>'0',
                'name' => 'Demo Category 3', 
                'slug' => \Helpers::createSlug('Demo Category 3','category',0,false),
                'color'=> '#000000',
                'order'=> '1',
                'status'=> '1',
                'is_featured'=> '1'
            )
        ];
        $i=0;
        foreach ($categoryArr as $row) {
            $id = Category::insertGetID($row);   
            $i++;
            $blogTransArr = array(
                'category_id'=>$row['id'],
                'language_code'=>'en',
                'name' => $row['name'], 
                'created_at'=> date('Y-m-d H:i:s')
            );
            CategoryTranslation::insertGetID($blogTransArr);
            $blogCatArr = array(
                'category_id'=>$row['id'],
                'blog_id' => $i, 
                'type' => 'category', 
                'created_at'=> date('Y-m-d H:i:s')
            );
            BlogCategory::insertGetID($blogCatArr);
        }
        // $categoryArr = array(
        //     'parent_id'=>'0',
        // 	'name' => 'Demo Category', 
        // 	'slug' => \Helpers::createSlug('Demo Category','category',0,false),
        //     'color'=> '#000000',
        //     'order'=> '1',
        //     'status'=> '1',
        //     'is_featured'=> '1'
        // );
        // $id = Category::insertGetID($categoryArr);
        // if($id){
        //     $blogTransArr = array(
        //         'category_id'=>'1',
        //         'language_code'=>'en',
        //         'name' => 'Demo Category', 
        //         'created_at'=> date('Y-m-d H:i:s')
        //     );
        //     CategoryTranslation::insertGetID($blogTransArr);
        //     $blogCatArr = array(
        //         'category_id'=>'1',
        //         'blog_id' => '1', 
        //         'type' => 'category', 
        //         'created_at'=> date('Y-m-d H:i:s')
        //     );
        //     BlogCategory::insertGetID($blogCatArr);
        // }
    }
}
