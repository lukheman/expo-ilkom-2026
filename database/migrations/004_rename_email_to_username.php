<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _004_rename_email_to_username
{
    public function up()
    {
        if (Capsule::schema()->hasColumn('users', 'email') && ! Capsule::schema()->hasColumn('users', 'username')) {
            Capsule::schema()->table('users', function ($t) {
                $t->string('username')->nullable();
            });
            Capsule::table('users')->update(['username' => Capsule::raw('email')]);
            Capsule::schema()->table('users', function ($t) {
                $t->dropColumn('email');
            });
        }
    }
}
