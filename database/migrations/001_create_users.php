<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _001_create_users
{
    public function up()
    {
        if (!Capsule::schema()->hasTable('users')) {
            Capsule::schema()->create('users', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email');
                $table->timestamps();
            });
        }
    }
}
