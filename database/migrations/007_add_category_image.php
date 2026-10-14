<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class _007_add_category_image
{
    public function up()
    {
        if (! Capsule::schema()->hasColumn('categories', 'image')) {
            Capsule::schema()->table('categories', function ($t) {
                $t->string('image')->nullable();
            });
        }
    }
}
