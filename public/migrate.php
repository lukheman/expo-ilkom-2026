<?php

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

use Core\Database;
use Core\Migration;

// Init Database
Database::init();

// Run Migrations
$migration = new Migration;
$migration->run();
