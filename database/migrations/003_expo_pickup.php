<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _003_expo_pickup
{
    public function up()
    {
        if (! Capsule::schema()->hasColumn('stores', 'booth_number')) {
            Capsule::schema()->table('stores', function ($t) {
                $t->string('booth_number')->nullable();
                $t->string('qris_image')->nullable();
            });
        }
        if (! Capsule::schema()->hasColumn('orders', 'store_id')) {
            Capsule::schema()->table('orders', function ($t) {
                $t->unsignedInteger('store_id')->nullable();
                $t->string('group_code')->nullable();
                $t->string('pickup_code')->nullable();
                $t->string('buyer_phone')->nullable();
                $t->string('fulfillment')->default('pickup');
                $t->string('payment_status')->default('Belum bayar');
            });
        }
        if (! Capsule::schema()->hasColumn('order_items', 'variant')) {
            Capsule::schema()->table('order_items', function ($t) {
                $t->string('variant')->nullable();
            });
        }
    }
}
