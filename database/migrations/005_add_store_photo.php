<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _005_add_store_photo
{
    public function up()
    {
        if (! Capsule::schema()->hasColumn('stores', 'photo')) {
            Capsule::schema()->table('stores', function ($t) {
                $t->string('photo')->nullable();
            });
        }
    }
}
