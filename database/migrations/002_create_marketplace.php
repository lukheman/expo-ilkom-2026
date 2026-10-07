<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _002_create_marketplace
{
    public function up()
    {
        if (! Capsule::schema()->hasColumn('users', 'role')) {
            Capsule::schema()->table('users', function ($t) {
                $t->string('password')->nullable();
                $t->string('role')->default('buyer');
                $t->string('status')->default('active');
            });
        }
        if (! Capsule::schema()->hasTable('categories')) {
            Capsule::schema()->create('categories', function ($t) {
                $t->increments('id');
                $t->string('name');
                $t->string('slug');
                $t->timestamps();
            });
        }
        if (! Capsule::schema()->hasTable('stores')) {
            Capsule::schema()->create('stores', function ($t) {
                $t->increments('id');
                $t->unsignedInteger('user_id');
                $t->string('name');
                $t->text('description')->nullable();
                $t->string('address')->nullable();
                $t->string('opening_hours')->nullable();
                $t->boolean('approved')->default(false);
                $t->timestamps();
            });
        }
        if (! Capsule::schema()->hasTable('products')) {
            Capsule::schema()->create('products', function ($t) {
                $t->increments('id');
                $t->unsignedInteger('store_id');
                $t->unsignedInteger('category_id')->nullable();
                $t->string('name');
                $t->text('description')->nullable();
                $t->decimal('price', 12, 2);
                $t->integer('stock')->default(0);
                $t->string('image')->nullable();
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (! Capsule::schema()->hasTable('orders')) {
            Capsule::schema()->create('orders', function ($t) {
                $t->increments('id');
                $t->unsignedInteger('user_id')->nullable();
                $t->string('customer_name');
                $t->text('address');
                $t->string('payment_method');
                $t->decimal('total', 12, 2);
                $t->string('status')->default('Menunggu');
                $t->text('note')->nullable();
                $t->timestamps();
            });
        }
        if (! Capsule::schema()->hasTable('order_items')) {
            Capsule::schema()->create('order_items', function ($t) {
                $t->increments('id');
                $t->unsignedInteger('order_id');
                $t->unsignedInteger('product_id');
                $t->integer('quantity');
                $t->decimal('price', 12, 2);
                $t->timestamps();
            });
        }
    }
}
