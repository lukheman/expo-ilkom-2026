<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['store_id', 'category_id', 'name', 'description', 'price', 'stock', 'image', 'active'];

    protected $casts = ['price' => 'float', 'active' => 'boolean'];
}
