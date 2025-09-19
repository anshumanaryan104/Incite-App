<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogVisibility extends Model
{
    use HasFactory;
    protected $table = "blog_visibilities";

    public function visibility(){
        return $this->hasOne('App\Models\Visibility',"id","visibility_id");
    }
}
