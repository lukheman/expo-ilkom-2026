<?php

namespace Core;

class Migration
{
    public function run()
    {
        $files = glob(__DIR__ . '/../database/migrations/*.php');

        foreach ($files as $file) {
            require_once $file;

            $class = basename($file, '.php');
            if (is_numeric($class[0])) {
                $class = '_' . $class;
            }
            $migration = new $class;
            $migration->up();

            echo "Migrated: $class\n";
        }
    }
}
