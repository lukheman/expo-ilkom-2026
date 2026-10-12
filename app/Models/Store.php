<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'stores';

    protected $fillable = ['user_id', 'name', 'description', 'approved', 'booth_number', 'photo', 'qris_image'];
}
