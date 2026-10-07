<?php
/**
 * MINI MVC FRAMEWORK SKELETON (UPGRADED)
 * + TWIG TEMPLATE ENGINE
 * + ELOQUENT ORM (Illuminate Database)
 * + MIGRATION SYSTEM
 */

// ==============================
// Front Controller
// ==============================

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use Core\App;
use Core\Database;

// Init Eloquent
Database::init();

$app = new App();
