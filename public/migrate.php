<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Database;
use Core\Migration;

// Init Database
Database::init();

// Run Migrations
$migration = new Migration();
$migration->run();
