<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _004_drop_store_address_hours
{
    public function up()
    {
        if (Capsule::schema()->hasColumn('stores', 'address')) {
            Capsule::schema()->table('stores', function ($t) {
                $t->dropColumn('address');
            });
        }
        if (Capsule::schema()->hasColumn('stores', 'opening_hours')) {
            Capsule::schema()->table('stores', function ($t) {
                $t->dropColumn('opening_hours');
            });
        }
    }
}
