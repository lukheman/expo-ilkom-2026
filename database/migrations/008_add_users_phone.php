<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _008_add_users_phone
{
    public function up()
    {
        if (! Capsule::schema()->hasColumn('users', 'phone')) {
            Capsule::schema()->table('users', function ($t) {
                $t->string('phone', 20)->nullable();
            });
        }
    }
}
