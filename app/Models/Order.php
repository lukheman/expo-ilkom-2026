<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = ['user_id', 'store_id', 'group_code', 'pickup_code', 'customer_name', 'buyer_phone', 'address', 'fulfillment', 'payment_method', 'payment_status', 'total', 'status', 'note'];
}
