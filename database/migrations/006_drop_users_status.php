<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _006_drop_users_status
{
    public function up()
    {
        if (Capsule::schema()->hasColumn('users', 'status')) {
            Capsule::schema()->table('users', function ($t) {
                $t->dropColumn('status');
            });
        }
    }
}
